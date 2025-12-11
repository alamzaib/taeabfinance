<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PackageController extends Controller
{
    /**
     * Get all available packages
     */
    public function index()
    {
        $packages = Package::where('active', true)
            ->orderBy('popular', 'desc')
            ->orderBy('price', 'asc')
            ->get()
            ->map(function ($package) {
                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'price' => (float) $package->price,
                    'currency' => $package->currency,
                    'period' => $package->period,
                    'features' => $package->features ?? [],
                    'popular' => (bool) $package->popular,
                    'active' => (bool) $package->active,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'packages' => $packages,
            ],
        ]);
    }

    /**
     * Request a package (creates pending payment for admin approval)
     */
    public function request(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
        ]);

        $package = Package::findOrFail($validated['package_id']);

        // Check if user already has a pending or active payment for this package
        $existingPayment = Payment::where('user_id', auth()->id())
            ->where('package_id', $package->id)
            ->whereIn('status', ['pending', 'completed'])
            ->first();

        if ($existingPayment) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a request or active subscription for this package.',
            ], 400);
        }

        // Create pending payment request
        $payment = Payment::create([
            'user_id' => auth()->id(),
            'package_id' => $package->id,
            'transaction_id' => 'REQ-' . strtoupper(Str::random(12)),
            'amount' => $package->price,
            'currency' => $package->currency,
            'status' => 'pending',
            'payment_method' => 'request',
            'metadata' => [
                'requested_at' => now()->toISOString(),
                'package_name' => $package->name,
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Package request submitted successfully. Your request is pending approval.',
            'data' => [
                'payment' => [
                    'id' => $payment->id,
                    'transaction_id' => $payment->transaction_id,
                    'package_name' => $package->name,
                    'amount' => $payment->amount,
                    'status' => $payment->status,
                ],
            ],
        ]);
    }
}

