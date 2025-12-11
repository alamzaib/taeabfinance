<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Earning;
use App\Models\User;
use App\Models\Package;
use Carbon\Carbon;

class EarningSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $packages = Package::all();

        if ($users->isEmpty() || $packages->isEmpty()) {
            $this->command->warn('No users or packages found. Please seed users and packages first.');
            return;
        }

        $types = ['dividend', 'interest', 'profit', 'bonus', 'referral'];
        $descriptions = [
            'dividend' => ['Quarterly Dividend Payment', 'Annual Dividend Distribution', 'Monthly Dividend'],
            'interest' => ['Savings Account Interest', 'Fixed Deposit Interest', 'Compound Interest'],
            'profit' => ['Investment Profit Share', 'Portfolio Profit Distribution', 'Trading Profit'],
            'bonus' => ['Loyalty Bonus', 'Performance Bonus', 'Welcome Bonus'],
            'referral' => ['Referral Commission', 'Referral Bonus', 'Affiliate Earnings'],
        ];

        foreach ($users as $user) {
            // Create earnings for the last 6 months
            for ($month = 0; $month < 6; $month++) {
                $earnedDate = Carbon::now()->subMonths($month);
                
                // Create 2-5 earnings per month
                $earningsCount = rand(2, 5);
                
                for ($i = 0; $i < $earningsCount; $i++) {
                    $type = $types[array_rand($types)];
                    $typeDescriptions = $descriptions[$type];
                    $description = $typeDescriptions[array_rand($typeDescriptions)];
                    
                    // Random date within the month
                    $day = rand(1, $earnedDate->daysInMonth);
                    $date = $earnedDate->copy()->day($day);
                    
                    // Random amount based on type
                    $amount = match($type) {
                        'dividend' => rand(50, 500),
                        'interest' => rand(10, 200),
                        'profit' => rand(100, 1000),
                        'bonus' => rand(25, 250),
                        'referral' => rand(50, 300),
                        default => rand(10, 500),
                    };
                    
                    Earning::create([
                        'user_id' => $user->id,
                        'package_id' => $packages->random()->id,
                        'type' => $type,
                        'description' => $description,
                        'amount' => $amount,
                        'currency' => 'USD',
                        'status' => 'completed',
                        'earned_date' => $date,
                        'metadata' => [
                            'source' => 'automated',
                            'seeded' => true,
                        ],
                    ]);
                }
            }
        }

        $this->command->info('Earnings seeded successfully.');
    }
}
