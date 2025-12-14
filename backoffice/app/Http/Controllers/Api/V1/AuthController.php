<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PaymentMethod;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user and return token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $token,
            ],
        ]);
    }

    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'ref' => 'nullable|string', // Referral code
        ]);

        // Find referrer if ref code provided
        $referredBy = null;
        $refCode = $request->input('ref');
        if ($refCode) {
            $affiliateLink = \App\Models\AffiliateLink::where('affiliate_code', $refCode)->first();
            if ($affiliateLink && $affiliateLink->active) {
                $referredBy = $affiliateLink->user_id;
                // Increment signups
                $affiliateLink->increment('signups');
                
                \Log::info('Affiliate registration', [
                    'ref_code' => $refCode,
                    'referrer_id' => $referredBy,
                    'new_user_email' => $request->email,
                ]);
            } else {
                \Log::warning('Invalid affiliate code used for registration', [
                    'ref_code' => $refCode,
                    'email' => $request->email,
                ]);
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'referred_by' => $referredBy,
        ]);

        // Create affiliate link for new user
        $this->createAffiliateLink($user);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $token,
            ],
        ], 201);
    }

    /**
     * Create affiliate link for user
     */
    private function createAffiliateLink(User $user)
    {
        $code = \App\Models\AffiliateLink::generateCode($user->id);
        $link = \App\Models\AffiliateLink::generateLink($code);

        \App\Models\AffiliateLink::create([
            'user_id' => $user->id,
            'affiliate_code' => $code,
            'affiliate_link' => $link,
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                ],
            ],
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful',
        ]);
    }

    /**
     * Get billing information
     */
    public function billing(Request $request)
    {
        $user = $request->user();
        
        // Get active payment/package
        $activePayment = Payment::where('user_id', $user->id)
            ->where('status', 'completed')
            ->with('package')
            ->latest()
            ->first();

        // Get payment methods
        $paymentMethods = PaymentMethod::where('user_id', $user->id)
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get billing history
        $billingHistory = Payment::where('user_id', $user->id)
            ->where('status', 'completed')
            ->with('package')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'date' => $payment->created_at->toDateString(),
                    'description' => $payment->package ? $payment->package->name : 'Payment',
                    'amount' => $payment->amount,
                    'status' => $payment->status,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'current_plan' => $activePayment && $activePayment->package 
                    ? $activePayment->package->name 
                    : 'No active plan',
                'amount' => $activePayment ? $activePayment->amount : 0,
                'currency' => $activePayment ? $activePayment->currency : 'USD',
                'next_billing_date' => $activePayment 
                    ? now()->addMonth()->format('Y-m-d')
                    : null,
                'billing_history' => $billingHistory,
                'payment_methods' => $paymentMethods->map(function ($method) {
                    return [
                        'id' => $method->id,
                        'type' => $method->type,
                        'card_type' => $method->card_type,
                        'last_four' => $method->last_four,
                        'exp_month' => $method->exp_month,
                        'exp_year' => $method->exp_year,
                        'holder_name' => $method->holder_name,
                        'is_primary' => $method->is_primary,
                        'display' => $this->formatCardDisplay($method),
                    ];
                }),
            ],
        ]);
    }

    /**
     * Format card for display
     */
    private function formatCardDisplay($paymentMethod)
    {
        $cardType = ucfirst($paymentMethod->card_type ?? 'Card');
        $lastFour = $paymentMethod->last_four;
        $exp = $paymentMethod->exp_month && $paymentMethod->exp_year 
            ? $paymentMethod->exp_month . '/' . substr($paymentMethod->exp_year, -2)
            : 'N/A';

        return "{$cardType} •••• {$lastFour} • Expires {$exp}";
    }
}

