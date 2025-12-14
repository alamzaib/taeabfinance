<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\AffiliateCommission;
use App\Models\AffiliateConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Handle Stripe webhook events
     */
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            Log::error('Stripe webhook: Invalid payload', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            Log::error('Stripe webhook: Invalid signature', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event->data->object);
                break;
            
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;
            
            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event->data->object);
                break;
            
            default:
                Log::info('Stripe webhook: Unhandled event type', ['type' => $event->type]);
        }

        return response()->json(['received' => true]);
    }

    /**
     * Handle checkout session completed
     */
    protected function handleCheckoutSessionCompleted($session)
    {
        try {
            $payment = Payment::where('stripe_session_id', $session->id)->first();

            if (!$payment) {
                Log::warning('Stripe webhook: Payment not found for session', ['session_id' => $session->id]);
                return;
            }

            // Update payment status
            $payment->update([
                'status' => 'completed',
                'stripe_payment_intent_id' => $session->payment_intent ?? null,
                'paid_at' => now(),
                'metadata' => array_merge($payment->metadata ?? [], [
                    'stripe_session_completed_at' => now()->toISOString(),
                    'stripe_customer_id' => $session->customer ?? null,
                ]),
            ]);

            // Store customer ID if available
            if ($session->customer) {
                $payment->update(['stripe_customer_id' => $session->customer]);
            }

            // Generate affiliate commission if user was referred
            $this->generateAffiliateCommission($payment);

            Log::info('Stripe webhook: Payment completed', [
                'payment_id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe webhook: Error handling checkout session completed', [
                'error' => $e->getMessage(),
                'session_id' => $session->id,
            ]);
        }
    }

    /**
     * Handle payment intent succeeded
     */
    protected function handlePaymentIntentSucceeded($paymentIntent)
    {
        try {
            $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)
                ->orWhere('stripe_session_id', $paymentIntent->metadata->session_id ?? null)
                ->first();

            if ($payment && $payment->status !== 'completed') {
                $payment->update([
                    'status' => 'completed',
                    'stripe_payment_intent_id' => $paymentIntent->id,
                    'paid_at' => now(),
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'payment_intent_succeeded_at' => now()->toISOString(),
                    ]),
                ]);

                Log::info('Stripe webhook: Payment intent succeeded', [
                    'payment_id' => $payment->id,
                    'payment_intent_id' => $paymentIntent->id,
                ]);

                // Generate affiliate commission if user was referred
                $this->generateAffiliateCommission($payment);
            }
        } catch (\Exception $e) {
            Log::error('Stripe webhook: Error handling payment intent succeeded', [
                'error' => $e->getMessage(),
                'payment_intent_id' => $paymentIntent->id,
            ]);
        }
    }

    /**
     * Handle payment intent failed
     */
    protected function handlePaymentIntentFailed($paymentIntent)
    {
        try {
            $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)
                ->orWhere('stripe_session_id', $paymentIntent->metadata->session_id ?? null)
                ->first();

            if ($payment) {
                $payment->update([
                    'status' => 'failed',
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'payment_intent_failed_at' => now()->toISOString(),
                        'failure_reason' => $paymentIntent->last_payment_error->message ?? 'Unknown error',
                    ]),
                ]);

                Log::warning('Stripe webhook: Payment intent failed', [
                    'payment_id' => $payment->id,
                    'payment_intent_id' => $paymentIntent->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Stripe webhook: Error handling payment intent failed', [
                'error' => $e->getMessage(),
                'payment_intent_id' => $paymentIntent->id,
            ]);
        }
    }

    /**
     * Generate affiliate commission for completed payment
     */
    protected function generateAffiliateCommission(Payment $payment)
    {
        try {
            $user = $payment->user;
            
            // Check if user was referred
            if (!$user->referred_by) {
                return;
            }

            // Get commission configuration
            $commissionRate = (float) AffiliateConfig::getValue('commission_rate', 10);
            $commissionType = AffiliateConfig::getValue('commission_type', 'percentage');
            $minPayment = (float) AffiliateConfig::getValue('min_payment_for_commission', 0);

            // Check minimum payment threshold
            if ($payment->amount < $minPayment) {
                return;
            }

            // Calculate commission
            $commissionAmount = 0;
            if ($commissionType === 'percentage') {
                $commissionAmount = ($payment->amount * $commissionRate) / 100;
            } else {
                $commissionAmount = (float) AffiliateConfig::getValue('fixed_commission_amount', 0);
            }

            // Only create commission if amount is positive and commission doesn't already exist
            if ($commissionAmount > 0) {
                // Check if commission already exists for this payment
                $existingCommission = AffiliateCommission::where('payment_id', $payment->id)->first();
                
                if (!$existingCommission) {
                    AffiliateCommission::create([
                        'referrer_id' => $user->referred_by,
                        'referred_id' => $user->id,
                        'payment_id' => $payment->id,
                        'commission_amount' => $commissionAmount,
                        'commission_type' => $commissionType,
                        'commission_rate' => $commissionType === 'percentage' ? $commissionRate : null,
                        'status' => 'pending',
                    ]);

                    Log::info('Affiliate commission created', [
                        'referrer_id' => $user->referred_by,
                        'referred_id' => $user->id,
                        'payment_id' => $payment->id,
                        'commission_amount' => $commissionAmount,
                    ]);
                } else {
                    Log::info('Affiliate commission already exists for payment', [
                        'payment_id' => $payment->id,
                        'commission_id' => $existingCommission->id,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error generating affiliate commission', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id,
            ]);
        }
    }
}
