<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;

class StripePaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Generate Stripe payment link for a pending payment
     */
    public function generatePaymentLink(Request $request, Payment $payment)
    {
        $request->validate([
            'success_url' => 'nullable|url',
            'cancel_url' => 'nullable|url',
        ]);

        // Only allow generating links for pending payments
        if ($payment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Payment link can only be generated for pending payments.',
            ], 400);
        }

        try {
            $payment->load(['user', 'package']);

            // Build success and cancel URLs
            $baseUrl = config('app.url');
            $successUrl = $request->input('success_url', $baseUrl . '/payment/success?session_id={CHECKOUT_SESSION_ID}');
            $cancelUrl = $request->input('cancel_url', $baseUrl . '/payment/cancel');

            // Create Stripe Checkout Session
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($payment->currency),
                        'product_data' => [
                            'name' => $payment->package ? $payment->package->name : 'Package Payment',
                            'description' => $payment->package ? 'Investment Package: ' . $payment->package->name : 'Payment for package',
                        ],
                        'unit_amount' => (int)($payment->amount * 100), // Convert to cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'customer_email' => $payment->user->email ?? null,
                'metadata' => [
                    'payment_id' => $payment->id,
                    'user_id' => $payment->user_id,
                    'package_id' => $payment->package_id,
                    'transaction_id' => $payment->transaction_id,
                ],
            ]);

            // Update payment with Stripe session info
            $payment->update([
                'stripe_session_id' => $session->id,
                'payment_link' => $session->url,
                'payment_method' => 'stripe',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment link generated successfully.',
                'data' => [
                    'payment_link' => $session->url,
                    'session_id' => $session->id,
                ],
            ]);
        } catch (ApiErrorException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate payment link: ' . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment link status
     */
    public function getPaymentStatus(Payment $payment)
    {
        if (!$payment->stripe_session_id) {
            return response()->json([
                'success' => false,
                'message' => 'No Stripe session found for this payment.',
            ], 404);
        }

        try {
            $session = Session::retrieve($payment->stripe_session_id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'session_status' => $session->payment_status,
                    'payment_status' => $payment->status,
                    'payment_link' => $payment->payment_link,
                ],
            ]);
        } catch (ApiErrorException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment status: ' . $e->getMessage(),
            ], 500);
        }
    }
}
