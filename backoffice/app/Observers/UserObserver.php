<?php

namespace App\Observers;

use App\Models\User;
use App\Models\AffiliateCommission;
use App\Models\AffiliateConfig;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user)
    {
        // Check if user was referred
        if ($user->referred_by) {
            $this->generateSignupCommission($user);
        }
    }

    /**
     * Generate affiliate commission for user signup
     */
    protected function generateSignupCommission(User $user)
    {
        try {
            Log::info('UserObserver: Checking signup commission generation', [
                'user_id' => $user->id,
                'referred_by' => $user->referred_by,
            ]);

            // Check if commission already exists for this user signup
            $existingCommission = AffiliateCommission::where('referred_id', $user->id)
                ->whereNull('payment_id')
                ->first();
            
            if ($existingCommission) {
                Log::info('UserObserver: Skipping - signup commission already exists', [
                    'user_id' => $user->id,
                    'commission_id' => $existingCommission->id,
                ]);
                return;
            }

            // Get commission configuration
            $commissionType = AffiliateConfig::getValue('commission_type', 'percentage');
            
            // For signup commissions, we'll use a fixed amount or signup bonus
            // Check if there's a signup-specific config, otherwise use fixed commission amount
            $signupBonusAmount = (float) AffiliateConfig::getValue('signup_bonus_amount', 0);
            $fixedCommissionAmount = (float) AffiliateConfig::getValue('fixed_commission_amount', 0);
            
            // Use signup bonus if set, otherwise use fixed commission amount, otherwise default to 0
            $commissionAmount = $signupBonusAmount > 0 ? $signupBonusAmount : $fixedCommissionAmount;

            Log::info('UserObserver: Commission config', [
                'user_id' => $user->id,
                'commission_type' => $commissionType,
                'signup_bonus_amount' => $signupBonusAmount,
                'fixed_commission_amount' => $fixedCommissionAmount,
                'commission_amount' => $commissionAmount,
            ]);

            // Only create commission if amount is positive
            if ($commissionAmount > 0) {
                $commission = AffiliateCommission::create([
                    'referrer_id' => $user->referred_by,
                    'referred_id' => $user->id,
                    'payment_id' => null, // No payment for signup commission
                    'commission_amount' => $commissionAmount,
                    'commission_type' => 'fixed', // Signup commissions are always fixed
                    'commission_rate' => null,
                    'status' => 'pending',
                ]);

                Log::info('Affiliate signup commission created via observer', [
                    'commission_id' => $commission->id,
                    'referrer_id' => $user->referred_by,
                    'referred_id' => $user->id,
                    'commission_amount' => $commissionAmount,
                ]);
            } else {
                Log::info('UserObserver: Skipping - commission amount is zero', [
                    'user_id' => $user->id,
                    'signup_bonus_amount' => $signupBonusAmount,
                    'fixed_commission_amount' => $fixedCommissionAmount,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error generating affiliate signup commission via observer', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}

