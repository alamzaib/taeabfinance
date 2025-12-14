<?php

namespace App\Observers;

use App\Models\Payment;
use App\Models\AffiliateCommission;
use App\Models\AffiliateConfig;
use Illuminate\Support\Facades\Log;

class PaymentObserver
{
    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment)
    {
        // Check if payment was created with 'completed' status
        if ($payment->status === 'completed') {
            $this->generateAffiliateCommission($payment);
        }
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment)
    {
        // Check if payment status changed to 'completed'
        if ($payment->isDirty('status') && $payment->status === 'completed') {
            $this->generateAffiliateCommission($payment);
        }
    }

    /**
     * Generate affiliate commission for completed payment
     */
    protected function generateAffiliateCommission(Payment $payment)
    {
        try {
            Log::info('PaymentObserver: Checking commission generation', [
                'payment_id' => $payment->id,
                'payment_status' => $payment->status,
            ]);

            $user = $payment->user;
            
            // Check if user was referred
            if (!$user || !$user->referred_by) {
                Log::info('PaymentObserver: Skipping - user not referred', [
                    'payment_id' => $payment->id,
                    'user_id' => $user ? $user->id : null,
                    'referred_by' => $user ? $user->referred_by : null,
                ]);
                return;
            }

            // Check if commission already exists for this payment
            $existingCommission = AffiliateCommission::where('payment_id', $payment->id)->first();
            if ($existingCommission) {
                Log::info('PaymentObserver: Skipping - commission already exists', [
                    'payment_id' => $payment->id,
                    'commission_id' => $existingCommission->id,
                ]);
                return;
            }

            // Get commission configuration
            $commissionRate = (float) AffiliateConfig::getValue('commission_rate', 10);
            $commissionType = AffiliateConfig::getValue('commission_type', 'percentage');
            $minPayment = (float) AffiliateConfig::getValue('min_payment_for_commission', 0);

            Log::info('PaymentObserver: Commission config', [
                'payment_id' => $payment->id,
                'commission_type' => $commissionType,
                'commission_rate' => $commissionRate,
                'fixed_commission_amount' => AffiliateConfig::getValue('fixed_commission_amount', 0),
                'min_payment' => $minPayment,
                'payment_amount' => $payment->amount,
            ]);

            // Check minimum payment threshold
            if ($payment->amount < $minPayment) {
                Log::info('PaymentObserver: Skipping - amount below minimum', [
                    'payment_id' => $payment->id,
                    'payment_amount' => $payment->amount,
                    'min_payment' => $minPayment,
                ]);
                return;
            }

            // Calculate commission
            $commissionAmount = 0;
            if ($commissionType === 'percentage') {
                $commissionAmount = ($payment->amount * $commissionRate) / 100;
            } else {
                $commissionAmount = (float) AffiliateConfig::getValue('fixed_commission_amount', 0);
            }

            Log::info('PaymentObserver: Calculated commission', [
                'payment_id' => $payment->id,
                'commission_amount' => $commissionAmount,
            ]);

            // Only create commission if amount is positive
            if ($commissionAmount > 0) {
                $commission = AffiliateCommission::create([
                    'referrer_id' => $user->referred_by,
                    'referred_id' => $user->id,
                    'payment_id' => $payment->id,
                    'commission_amount' => $commissionAmount,
                    'commission_type' => $commissionType,
                    'commission_rate' => $commissionType === 'percentage' ? $commissionRate : null,
                    'status' => 'pending',
                ]);

                Log::info('Affiliate commission created via observer', [
                    'commission_id' => $commission->id,
                    'referrer_id' => $user->referred_by,
                    'referred_id' => $user->id,
                    'payment_id' => $payment->id,
                    'commission_amount' => $commissionAmount,
                ]);
            } else {
                Log::warning('PaymentObserver: Commission amount is zero or negative', [
                    'payment_id' => $payment->id,
                    'commission_amount' => $commissionAmount,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error generating affiliate commission via observer', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
