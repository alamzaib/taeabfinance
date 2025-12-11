<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaymentMethodController extends Controller
{
    /**
     * Get all payment methods for authenticated user
     */
    public function index(Request $request)
    {
        $paymentMethods = PaymentMethod::where('user_id', $request->user()->id)
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($method) {
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
            });

        return response()->json([
            'success' => true,
            'data' => [
                'payment_methods' => $paymentMethods,
            ],
        ]);
    }

    /**
     * Store a new payment method
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:card',
            'card_number' => 'required|string|min:13|max:19',
            'exp_month' => 'required|string|size:2',
            'exp_year' => 'required|string|size:4',
            'holder_name' => 'required|string|max:255',
            'cvv' => 'required|string|size:3',
            'is_primary' => 'boolean',
        ]);

        // Extract last 4 digits
        $lastFour = substr($validated['card_number'], -4);
        
        // Determine card type from first digit
        $cardType = $this->detectCardType($validated['card_number']);

        // If this is set as primary, unset other primary methods
        if ($request->has('is_primary') && $request->is_primary) {
            PaymentMethod::where('user_id', $request->user()->id)
                ->update(['is_primary' => false]);
        }

        // If this is the first payment method, make it primary
        $isFirst = PaymentMethod::where('user_id', $request->user()->id)->count() === 0;
        $isPrimary = $isFirst || ($request->has('is_primary') && $request->is_primary);

        $paymentMethod = PaymentMethod::create([
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'card_type' => $cardType,
            'last_four' => $lastFour,
            'exp_month' => $validated['exp_month'],
            'exp_year' => $validated['exp_year'],
            'holder_name' => $validated['holder_name'],
            'is_primary' => $isPrimary,
            'metadata' => [
                'added_at' => now()->toISOString(),
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment method added successfully',
            'data' => [
                'payment_method' => [
                    'id' => $paymentMethod->id,
                    'type' => $paymentMethod->type,
                    'card_type' => $paymentMethod->card_type,
                    'last_four' => $paymentMethod->last_four,
                    'exp_month' => $paymentMethod->exp_month,
                    'exp_year' => $paymentMethod->exp_year,
                    'holder_name' => $paymentMethod->holder_name,
                    'is_primary' => $paymentMethod->is_primary,
                    'display' => $this->formatCardDisplay($paymentMethod),
                ],
            ],
        ], 201);
    }

    /**
     * Update a payment method
     */
    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        // Ensure user owns this payment method
        if ($paymentMethod->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'exp_month' => 'sometimes|string|size:2',
            'exp_year' => 'sometimes|string|size:4',
            'holder_name' => 'sometimes|string|max:255',
        ]);

        $paymentMethod->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Payment method updated successfully',
            'data' => [
                'payment_method' => [
                    'id' => $paymentMethod->id,
                    'type' => $paymentMethod->type,
                    'card_type' => $paymentMethod->card_type,
                    'last_four' => $paymentMethod->last_four,
                    'exp_month' => $paymentMethod->exp_month,
                    'exp_year' => $paymentMethod->exp_year,
                    'holder_name' => $paymentMethod->holder_name,
                    'is_primary' => $paymentMethod->is_primary,
                    'display' => $this->formatCardDisplay($paymentMethod),
                ],
            ],
        ]);
    }

    /**
     * Set payment method as primary
     */
    public function setPrimary(Request $request, PaymentMethod $paymentMethod)
    {
        // Ensure user owns this payment method
        if ($paymentMethod->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $paymentMethod->setAsPrimary();

        return response()->json([
            'success' => true,
            'message' => 'Payment method set as primary',
            'data' => [
                'payment_method' => [
                    'id' => $paymentMethod->id,
                    'is_primary' => true,
                ],
            ],
        ]);
    }

    /**
     * Delete a payment method
     */
    public function destroy(Request $request, PaymentMethod $paymentMethod)
    {
        // Ensure user owns this payment method
        if ($paymentMethod->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Prevent deletion of primary payment method
        if ($paymentMethod->is_primary) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete primary payment method. Please set another payment method as primary first.',
            ], 400);
        }

        $paymentMethod->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment method deleted successfully',
        ]);
    }

    /**
     * Detect card type from card number
     */
    private function detectCardType($cardNumber)
    {
        $firstDigit = substr($cardNumber, 0, 1);
        $firstTwo = substr($cardNumber, 0, 2);

        if ($firstDigit == '4') {
            return 'visa';
        } elseif ($firstTwo >= '51' && $firstTwo <= '55') {
            return 'mastercard';
        } elseif ($firstTwo == '34' || $firstTwo == '37') {
            return 'amex';
        } elseif ($firstTwo >= '60' && $firstTwo <= '65') {
            return 'discover';
        }

        return 'unknown';
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
