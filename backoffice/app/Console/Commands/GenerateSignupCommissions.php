<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\AffiliateCommission;
use App\Models\AffiliateConfig;

class GenerateSignupCommissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'affiliate:generate-signup-commissions {--user-id= : Generate commission for specific user ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate affiliate signup commissions for users who were referred but don\'t have signup commissions yet';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user-id');
        
        if ($userId) {
            $users = User::where('id', $userId)->whereNotNull('referred_by')->get();
        } else {
            // Get users who were referred but don't have signup commissions
            $usersWithCommissions = AffiliateCommission::whereNull('payment_id')
                ->pluck('referred_id')
                ->toArray();
            
            $users = User::whereNotNull('referred_by')
                ->whereNotIn('id', $usersWithCommissions)
                ->get();
        }

        if ($users->isEmpty()) {
            $this->info('No users found that need signup commissions generated.');
            return 0;
        }

        $this->info("Found {$users->count()} user(s) to process.");

        // Get commission configuration
        $signupBonusAmount = (float) AffiliateConfig::getValue('signup_bonus_amount', 0);
        $fixedCommissionAmount = (float) AffiliateConfig::getValue('fixed_commission_amount', 0);
        
        // Use signup bonus if set, otherwise use fixed commission amount
        $commissionAmount = $signupBonusAmount > 0 ? $signupBonusAmount : $fixedCommissionAmount;

        if ($commissionAmount <= 0) {
            $this->warn('Signup bonus amount and fixed commission amount are both 0. Please set a signup bonus amount in the affiliate config.');
            return 1;
        }

        $this->info("Using commission amount: \${$commissionAmount}");

        $generated = 0;
        $skipped = 0;

        foreach ($users as $user) {
            // Check if commission already exists
            $existingCommission = AffiliateCommission::where('referred_id', $user->id)
                ->whereNull('payment_id')
                ->first();
            
            if ($existingCommission) {
                $skipped++;
                $this->line("Skipping User #{$user->id} ({$user->email}): Signup commission already exists");
                continue;
            }

            if ($commissionAmount > 0) {
                AffiliateCommission::create([
                    'referrer_id' => $user->referred_by,
                    'referred_id' => $user->id,
                    'payment_id' => null,
                    'commission_amount' => $commissionAmount,
                    'commission_type' => 'fixed',
                    'commission_rate' => null,
                    'status' => 'pending',
                ]);

                $generated++;
                $this->info("Generated signup commission for User #{$user->id} ({$user->email}): \${$commissionAmount}");
            } else {
                $skipped++;
                $this->line("Skipping User #{$user->id} ({$user->email}): Commission amount is zero");
            }
        }

        $this->info("\nCompleted: {$generated} signup commission(s) generated, {$skipped} skipped.");
        return 0;
    }
}

