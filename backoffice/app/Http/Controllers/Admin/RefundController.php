<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefundRequest;
use Illuminate\Http\Request;
use App\LogsActivity;

class RefundController extends Controller
{
    use LogsActivity;
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $refunds = RefundRequest::with(['user', 'payment'])->get()->map(function ($refund) {
                return [
                    'id' => $refund->id,
                    'user_name' => $refund->user->name ?? 'N/A',
                    'payment_id' => $refund->payment_id,
                    'reason' => $refund->reason,
                    'status' => $refund->status,
                    'created_at' => $refund->created_at->format('Y-m-d H:i:s'),
                ];
            });
            return response()->json($refunds);
        }
        return view('admin.refunds.index');
    }

    public function show(RefundRequest $refundRequest, Request $request)
    {
        $refundRequest->load(['user', 'payment', 'processor']);
        if ($request->ajax() || $request->wantsJson() || $request->accepts(['application/json'])) {
            return response()->json([
                'success' => true,
                'data' => [
                    'refundRequest' => [
                        'id' => $refundRequest->id,
                        'user' => $refundRequest->user ? ['id' => $refundRequest->user->id, 'name' => $refundRequest->user->name, 'email' => $refundRequest->user->email] : null,
                        'payment' => $refundRequest->payment ? ['id' => $refundRequest->payment->id, 'transaction_id' => $refundRequest->payment->transaction_id, 'amount' => $refundRequest->payment->amount] : null,
                        'reason' => $refundRequest->reason,
                        'description' => $refundRequest->description,
                        'status' => $refundRequest->status,
                        'admin_notes' => $refundRequest->admin_notes,
                        'processor' => $refundRequest->processor ? ['id' => $refundRequest->processor->id, 'name' => $refundRequest->processor->name] : null,
                        'processed_at' => $refundRequest->processed_at ? $refundRequest->processed_at->toDateTimeString() : null,
                        'created_at' => $refundRequest->created_at->toDateTimeString(),
                        'updated_at' => $refundRequest->updated_at->toDateTimeString(),
                    ]
                ]
            ]);
        }
        return view('admin.refunds.show', compact('refundRequest'));
    }

    public function approve(Request $request, RefundRequest $refundRequest)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string',
        ]);

        $oldValues = $refundRequest->toArray();
        $refundRequest->update([
            'status' => 'approved',
            'admin_notes' => $validated['admin_notes'] ?? null,
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);
        $newValues = $refundRequest->fresh()->toArray();
        
        // Log activity
        $this->logActivity('approve', 'Refund Requests', $refundRequest, null, $oldValues, $newValues);

        // Update payment status
        $paymentOldValues = $refundRequest->payment->toArray();
        $refundRequest->payment->update(['status' => 'refunded']);
        $paymentNewValues = $refundRequest->payment->fresh()->toArray();
        
        // Log payment update
        $this->logActivity('update', 'Payments', $refundRequest->payment, 'Payment refunded', $paymentOldValues, $paymentNewValues);

        return redirect()->back()->with('success', 'Refund approved successfully.');
    }

    public function reject(Request $request, RefundRequest $refundRequest)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string',
        ]);

        $oldValues = $refundRequest->toArray();
        $refundRequest->update([
            'status' => 'rejected',
            'admin_notes' => $validated['admin_notes'],
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);
        $newValues = $refundRequest->fresh()->toArray();
        
        // Log activity
        $this->logActivity('reject', 'Refund Requests', $refundRequest, null, $oldValues, $newValues);

        return redirect()->back()->with('success', 'Refund rejected.');
    }
}
