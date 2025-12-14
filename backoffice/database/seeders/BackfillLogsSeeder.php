<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Payment;
use App\Models\Package;
use App\Models\RefundRequest;
use App\Models\UserLog;
use App\Models\PaymentLog;
use App\Models\PackageLog;
use App\Models\RefundRequestLog;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Request;

class BackfillLogsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting to backfill logs...');

        // Backfill User Logs
        $this->command->info('Backfilling user logs...');
        $users = User::all();
        foreach ($users as $user) {
            $userData = $user->toArray();
            unset($userData['password']);
            
            // Create user log entry
            UserLog::create([
                'user_id' => $user->id,
                'changed_by' => null, // System/initial creation
                'action' => 'create',
                'old_values' => null,
                'new_values' => $userData,
                'description' => 'User created (backfilled)',
                'ip_address' => null,
                'user_agent' => null,
                'created_at' => $user->created_at,
                'updated_at' => $user->created_at,
            ]);

            // If user was updated, create update log
            if ($user->updated_at->gt($user->created_at)) {
                UserLog::create([
                    'user_id' => $user->id,
                    'changed_by' => null,
                    'action' => 'update',
                    'old_values' => null,
                    'new_values' => $userData,
                    'description' => 'User updated (backfilled)',
                    'ip_address' => null,
                    'user_agent' => null,
                    'created_at' => $user->updated_at,
                    'updated_at' => $user->updated_at,
                ]);
            }

            // Create activity log entry
            ActivityLog::create([
                'user_id' => null,
                'action' => 'create',
                'module' => 'Users',
                'model_type' => get_class($user),
                'model_id' => $user->id,
                'description' => 'User created (backfilled)',
                'old_values' => null,
                'new_values' => $userData,
                'ip_address' => null,
                'user_agent' => null,
                'route' => 'backfill',
                'request_data' => null,
                'created_at' => $user->created_at,
                'updated_at' => $user->created_at,
            ]);
        }
        $this->command->info("Created {$users->count()} user log entries");

        // Backfill Package Logs
        $this->command->info('Backfilling package logs...');
        $packages = Package::all();
        foreach ($packages as $package) {
            $packageData = $package->toArray();
            
            PackageLog::create([
                'package_id' => $package->id,
                'changed_by' => null,
                'action' => 'create',
                'old_values' => null,
                'new_values' => $packageData,
                'description' => 'Package created (backfilled)',
                'ip_address' => null,
                'user_agent' => null,
                'created_at' => $package->created_at,
                'updated_at' => $package->created_at,
            ]);

            if ($package->updated_at->gt($package->created_at)) {
                PackageLog::create([
                    'package_id' => $package->id,
                    'changed_by' => null,
                    'action' => 'update',
                    'old_values' => null,
                    'new_values' => $packageData,
                    'description' => 'Package updated (backfilled)',
                    'ip_address' => null,
                    'user_agent' => null,
                    'created_at' => $package->updated_at,
                    'updated_at' => $package->updated_at,
                ]);
            }

            ActivityLog::create([
                'user_id' => null,
                'action' => 'create',
                'module' => 'Packages',
                'model_type' => get_class($package),
                'model_id' => $package->id,
                'description' => 'Package created (backfilled)',
                'old_values' => null,
                'new_values' => $packageData,
                'ip_address' => null,
                'user_agent' => null,
                'route' => 'backfill',
                'request_data' => null,
                'created_at' => $package->created_at,
                'updated_at' => $package->created_at,
            ]);
        }
        $this->command->info("Created {$packages->count()} package log entries");

        // Backfill Payment Logs
        $this->command->info('Backfilling payment logs...');
        $payments = Payment::all();
        foreach ($payments as $payment) {
            $paymentData = $payment->toArray();
            
            PaymentLog::create([
                'payment_id' => $payment->id,
                'changed_by' => $payment->user_id, // User who created the payment
                'action' => 'create',
                'old_values' => null,
                'new_values' => $paymentData,
                'description' => 'Payment created (backfilled)',
                'ip_address' => null,
                'user_agent' => null,
                'created_at' => $payment->created_at,
                'updated_at' => $payment->created_at,
            ]);

            if ($payment->updated_at->gt($payment->created_at)) {
                PaymentLog::create([
                    'payment_id' => $payment->id,
                    'changed_by' => null,
                    'action' => 'update',
                    'old_values' => null,
                    'new_values' => $paymentData,
                    'description' => 'Payment updated (backfilled)',
                    'ip_address' => null,
                    'user_agent' => null,
                    'created_at' => $payment->updated_at,
                    'updated_at' => $payment->updated_at,
                ]);
            }

            ActivityLog::create([
                'user_id' => $payment->user_id,
                'action' => 'create',
                'module' => 'Payments',
                'model_type' => get_class($payment),
                'model_id' => $payment->id,
                'description' => 'Payment created (backfilled)',
                'old_values' => null,
                'new_values' => $paymentData,
                'ip_address' => null,
                'user_agent' => null,
                'route' => 'backfill',
                'request_data' => null,
                'created_at' => $payment->created_at,
                'updated_at' => $payment->created_at,
            ]);
        }
        $this->command->info("Created {$payments->count()} payment log entries");

        // Backfill Refund Request Logs
        $this->command->info('Backfilling refund request logs...');
        $refunds = RefundRequest::all();
        foreach ($refunds as $refund) {
            $refundData = $refund->toArray();
            
            RefundRequestLog::create([
                'refund_request_id' => $refund->id,
                'changed_by' => $refund->user_id,
                'action' => 'create',
                'old_values' => null,
                'new_values' => $refundData,
                'description' => 'Refund request created (backfilled)',
                'ip_address' => null,
                'user_agent' => null,
                'created_at' => $refund->created_at,
                'updated_at' => $refund->created_at,
            ]);

            if ($refund->updated_at->gt($refund->created_at)) {
                RefundRequestLog::create([
                    'refund_request_id' => $refund->id,
                    'changed_by' => $refund->processed_by,
                    'action' => $refund->status === 'approved' ? 'approve' : ($refund->status === 'rejected' ? 'reject' : 'update'),
                    'old_values' => null,
                    'new_values' => $refundData,
                    'description' => 'Refund request updated (backfilled)',
                    'ip_address' => null,
                    'user_agent' => null,
                    'created_at' => $refund->updated_at,
                    'updated_at' => $refund->updated_at,
                ]);
            }

            ActivityLog::create([
                'user_id' => $refund->user_id,
                'action' => 'create',
                'module' => 'Refund Requests',
                'model_type' => get_class($refund),
                'model_id' => $refund->id,
                'description' => 'Refund request created (backfilled)',
                'old_values' => null,
                'new_values' => $refundData,
                'ip_address' => null,
                'user_agent' => null,
                'route' => 'backfill',
                'request_data' => null,
                'created_at' => $refund->created_at,
                'updated_at' => $refund->created_at,
            ]);
        }
        $this->command->info("Created {$refunds->count()} refund request log entries");

        $this->command->info('Log backfilling completed!');
    }
}
