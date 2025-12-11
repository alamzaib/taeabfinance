<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Basic Plan',
                'price' => 9.99,
                'currency' => 'USD',
                'period' => 'month',
                'features' => [
                    'Track up to 50 transactions',
                    'Basic budgeting tools',
                    'Email support',
                    'Mobile app access',
                    'Monthly reports',
                ],
                'popular' => false,
                'active' => true,
            ],
            [
                'name' => 'Professional Plan',
                'price' => 19.99,
                'currency' => 'USD',
                'period' => 'month',
                'features' => [
                    'Unlimited transactions',
                    'Advanced budgeting & analytics',
                    'Priority support',
                    'Mobile app access',
                    'Custom reports',
                    'Export data',
                    'Multi-account support',
                ],
                'popular' => true,
                'active' => true,
            ],
            [
                'name' => 'Enterprise Plan',
                'price' => 49.99,
                'currency' => 'USD',
                'period' => 'month',
                'features' => [
                    'Everything in Professional',
                    'Dedicated account manager',
                    '24/7 phone support',
                    'Custom integrations',
                    'Team collaboration',
                    'Advanced security',
                    'API access',
                ],
                'popular' => false,
                'active' => true,
            ],
        ];

        foreach ($packages as $package) {
            Package::firstOrCreate(
                ['name' => $package['name']],
                $package
            );
        }

        $this->command->info('Packages seeded successfully.');
    }
}

