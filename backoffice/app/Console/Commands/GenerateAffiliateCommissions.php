<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Models\AffiliateCommission;
use App\Models\AffiliateConfig;

class GenerateAffiliateCommissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'affiliate:generate-commissions {--payment-id= : Generate commission for specific payment ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate affiliate commissions for completed payments that don\'t have commissions yet';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $paymentId = $this->option('payment-id');
        
        if ($paymentId) {
            $payments = Payment::where('id', $paymentId)->where('status', 'completed')->with('user')->get();
        } else {
            // Get completed payments that don't have commissions
            $paymentsWithCommissions = AffiliateCommission::pluck('payment_id')->toArray();
            $payments = Payment::where('status', 'completed')
                ->whereNotIn('id', $paymentsWithCommissions)
                ->with('user')
                ->get();
        }

        if ($payments->isEmpty()) {
            $this->info('No payments found that need commissions generated.');
            return 0;
        }

        $this->info("Found {$payments->count()} payment(s) to process.");

        $generated = 0;
        $skipped = 0;

        foreach ($payments as $payment) {
            $user = $payment->user;
            
            if (!$user || !$user->referred_by) {
                $skipped++;
                $this->line("Skipping Payment #{$payment->id}: User was not referred");
                continue;
            }

            // Check if commission already exists
            $existingCommission = AffiliateCommission::where('payment_id', $payment->id)->first();
            if ($existingCommission) {
                $skipped++;
                $this->line("Skipping Payment #{$payment->id}: Commission already exists");
                continue;
            }

            // Get commission configuration
            $commissionRate = (float) AffiliateConfig::getValue('commission_rate', 10);
            $commissionType = AffiliateConfig::getValue('commission_type', 'percentage');
            $minPayment = (float) AffiliateConfig::getValue('min_payment_for_commission', 0);

            // Check minimum payment threshold
            if ($payment->amount < $minPayment) {
                $skipped++;
                $this->line("Skipping Payment #{$payment->id}: Amount below minimum threshold");
                continue;
            }

            // Calculate commission
            $commissionAmount = 0;
            if ($commissionType === 'percentage') {
                $commissionAmount = ($payment->amount * $commissionRate) / 100;
            } else {
                $commissionAmount = (float) AffiliateConfig::getValue('fixed_commission_amount', 0);
            }

            if ($commissionAmount > 0) {
                AffiliateCommission::create([
                    'referrer_id' => $user->referred_by,
                    'referred_id' => $user->id,
                    'payment_id' => $payment->id,
                    'commission_amount' => $commissionAmount,
                    'commission_type' => $commissionType,
                    'commission_rate' => $commissionType === 'percentage' ? $commissionRate : null,
                    'status' => 'pending',
                ]);

                $generated++;
                $this->info("Generated commission for Payment #{$payment->id}: \${$commissionAmount}");
            } else {
                $skipped++;
                $this->line("Skipping Payment #{$payment->id}: Commission amount is zero");
            }
        }

        $this->info("\nCompleted: {$generated} commission(s) generated, {$skipped} skipped.");
        return 0;
    }
}
