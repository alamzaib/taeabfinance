<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $payments = Payment::with(['user', 'package'])->orderBy('created_at', 'desc')->get()->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'transaction_id' => $payment->transaction_id,
                    'user_name' => $payment->user->name ?? 'N/A',
                    'user_email' => $payment->user->email ?? 'N/A',
                    'package_name' => $payment->package->name ?? 'N/A',
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'status' => $payment->status,
                    'payment_method' => $payment->payment_method,
                    'payment_link' => $payment->payment_link,
                    'has_payment_link' => !empty($payment->payment_link),
                    'created_at' => $payment->created_at->format('Y-m-d H:i:s'),
                    'paid_at' => $payment->paid_at ? $payment->paid_at->format('Y-m-d H:i:s') : null,
                ];
            });
            return response()->json($payments);
        }
        return view('admin.payments.index');
    }

    public function show(Payment $payment, Request $request)
    {
        $payment->load(['user', 'package', 'refundRequests']);
        if ($request->ajax() || $request->wantsJson() || $request->accepts(['application/json'])) {
            return response()->json([
                'success' => true,
                'data' => [
                    'payment' => [
                        'id' => $payment->id,
                        'transaction_id' => $payment->transaction_id,
                        'user' => $payment->user ? ['id' => $payment->user->id, 'name' => $payment->user->name, 'email' => $payment->user->email] : null,
                        'package' => $payment->package ? ['id' => $payment->package->id, 'name' => $payment->package->name] : null,
                        'amount' => $payment->amount,
                        'currency' => $payment->currency,
                        'status' => $payment->status,
                        'payment_method' => $payment->payment_method,
                        'payment_link' => $payment->payment_link,
                        'stripe_session_id' => $payment->stripe_session_id,
                        'refund_requests_count' => $payment->refundRequests->count(),
                        'created_at' => $payment->created_at->toDateTimeString(),
                        'updated_at' => $payment->updated_at->toDateTimeString(),
                        'paid_at' => $payment->paid_at ? $payment->paid_at->toDateTimeString() : null,
                    ]
                ]
            ]);
        }
        return view('admin.payments.show', compact('payment'));
    }

    public function refund(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
            'description' => 'nullable|string',
        ]);

        // Create refund request
        $payment->refundRequests()->create([
            'user_id' => $payment->user_id,
            'reason' => $validated['reason'],
            'description' => $validated['description'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Refund request created successfully.');
    }
}
