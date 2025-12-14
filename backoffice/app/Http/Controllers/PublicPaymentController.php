<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PublicPaymentController extends Controller
{
    /**
     * Show payment page with payment link
     */
    public function show($token)
    {
        // Find payment by a secure token (we'll add this to payments table)
        // For now, we'll use transaction_id as token
        $payment = Payment::where('transaction_id', $token)
            ->orWhere('id', $token)
            ->with(['user', 'package'])
            ->first();

        if (!$payment) {
            abort(404, 'Payment not found');
        }

        // If payment is already completed, show success message
        if ($payment->status === 'completed') {
            return view('payment.completed', compact('payment'));
        }

        // If payment is failed, show error
        if ($payment->status === 'failed') {
            return view('payment.failed', compact('payment'));
        }

        // If no payment link exists, show pending message
        if (!$payment->payment_link) {
            return view('payment.pending', compact('payment'));
        }

        // Redirect to Stripe checkout
        return redirect($payment->payment_link);
    }

    /**
     * Payment success page
     */
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
        
        if ($sessionId) {
            $payment = Payment::where('stripe_session_id', $sessionId)->first();
            if ($payment) {
                return view('payment.success', compact('payment'));
            }
        }

        return view('payment.success');
    }

    /**
     * Payment cancel page
     */
    public function cancel()
    {
        return view('payment.cancel');
    }
}
