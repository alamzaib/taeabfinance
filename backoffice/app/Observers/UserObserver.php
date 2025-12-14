<?php

namespace App\Observers;

use App\Models\User;
use App\Models\AffiliateCommission;
use App\Models\AffiliateConfig;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user)
    {
        // Generate unsubscribe token for new user
        $user->update([
            'unsubscribe_token' => Str::random(32),
        ]);

        // Send welcome email to new user
        $this->sendRegistrationEmail($user);

        // Check if user was referred
        if ($user->referred_by) {
            $this->generateSignupCommission($user);
            $this->sendReferralSignupEmail($user);
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

    /**
     * Send registration welcome email to new user
     */
    protected function sendRegistrationEmail(User $user)
    {
        try {
            // Only skip if explicitly disabled (null or true means enabled by default)
            if ($user->email_notifications_enabled === false) {
                Log::info('Registration email skipped - user has disabled notifications', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
                return;
            }

            Notification::create([
                'user_id' => $user->id,
                'type' => 'user_registration',
                'to_email' => $user->email,
                'from_email' => config('mail.from.address', 'noreply@taeab.com'),
                'from_name' => config('mail.from.name', 'TAEAB'),
                'subject' => 'Welcome to TAEAB!',
                'message' => "Welcome to TAEAB, {$user->name}! Your account has been successfully created.",
                'data' => [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                ],
                'status' => 'pending',
            ]);

            Log::info('Registration email notification created', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating registration email notification', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
        }
    }

    /**
     * Send referral signup email to referrer
     */
    protected function sendReferralSignupEmail(User $newUser)
    {
        try {
            $referrer = User::find($newUser->referred_by);

            // Only skip if referrer doesn't exist or has explicitly disabled notifications
            if (!$referrer || $referrer->email_notifications_enabled === false) {
                if (!$referrer) {
                    Log::info('Referral signup email skipped - referrer not found', [
                        'referred_by' => $newUser->referred_by,
                    ]);
                } else {
                    Log::info('Referral signup email skipped - referrer has disabled notifications', [
                        'referrer_id' => $referrer->id,
                    ]);
                }
                return;
            }

            Notification::create([
                'user_id' => $referrer->id,
                'type' => 'referral_signup',
                'to_email' => $referrer->email,
                'from_email' => config('mail.from.address', 'noreply@taeab.com'),
                'from_name' => config('mail.from.name', 'TAEAB'),
                'subject' => '🎉 New Referral Signup - TAEAB',
                'message' => "Congratulations! Someone just signed up using your affiliate link.",
                'data' => [
                    'referrer_id' => $referrer->id,
                    'new_user' => [
                        'id' => $newUser->id,
                        'name' => $newUser->name,
                        'email' => $newUser->email,
                        'created_at' => $newUser->created_at->toDateTimeString(),
                    ],
                ],
                'status' => 'pending',
            ]);

            Log::info('Referral signup email notification created', [
                'referrer_id' => $referrer->id,
                'new_user_id' => $newUser->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating referral signup email notification', [
                'error' => $e->getMessage(),
                'new_user_id' => $newUser->id,
            ]);
        }
    }
}

