<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    /**
     * Get all available packages
     */
    public function index()
    {
        $packages = [
            [
                'id' => 1,
                'name' => 'Basic',
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
            ],
            [
                'id' => 2,
                'name' => 'Professional',
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
            ],
            [
                'id' => 3,
                'name' => 'Enterprise',
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
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'packages' => $packages,
            ],
        ]);
    }
}

