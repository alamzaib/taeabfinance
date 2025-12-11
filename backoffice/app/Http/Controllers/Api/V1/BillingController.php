<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    /**
     * Get billing summary and recent history
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get active payment/package
        $activePayment = Payment::where('user_id', $user->id)
            ->where('status', 'completed')
            ->with('package')
            ->latest()
            ->first();

        // Get total payments
        $totalPayments = Payment::where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('amount');

        // Get this month's payments
        $thisMonthPayments = Payment::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        // Get last month's payments
        $lastMonthPayments = Payment::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereYear('created_at', now()->subMonth()->year)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->sum('amount');

        // Calculate percentage change
        $percentageChange = $lastMonthPayments > 0 
            ? (($thisMonthPayments - $lastMonthPayments) / $lastMonthPayments) * 100 
            : ($thisMonthPayments > 0 ? 100 : 0);

        // Get payments by status
        $paymentsByStatus = Payment::where('user_id', $user->id)
            ->selectRaw('status, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status => [
                    'total' => (float) $item->total,
                    'count' => (int) $item->count,
                ]];
            });

        // Get recent payments
        $recentPayments = Payment::where('user_id', $user->id)
            ->where('status', 'completed')
            ->with('package')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'date' => $payment->created_at->format('Y-m-d'),
                    'description' => $payment->package ? $payment->package->name : 'Payment',
                    'amount' => (float) $payment->amount,
                    'currency' => $payment->currency,
                    'status' => $payment->status,
                    'package_name' => $payment->package ? $payment->package->name : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'current_plan' => $activePayment && $activePayment->package 
                    ? $activePayment->package->name 
                    : 'No active plan',
                'amount' => $activePayment ? (float) $activePayment->amount : 0,
                'currency' => $activePayment ? $activePayment->currency : 'USD',
                'next_billing_date' => $activePayment 
                    ? now()->addMonth()->format('Y-m-d')
                    : null,
                'total_payments' => (float) $totalPayments,
                'this_month_payments' => (float) $thisMonthPayments,
                'last_month_payments' => (float) $lastMonthPayments,
                'percentage_change' => round($percentageChange, 2),
                'payments_by_status' => $paymentsByStatus,
                'recent_payments' => $recentPayments,
            ],
        ]);
    }

    /**
     * Get billing history with pagination, sorting, and filtering.
     */
    public function history(Request $request)
    {
        $user = $request->user();
        $perPage = $request->get('per_page', 20);
        $status = $request->get('status');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $query = Payment::where('user_id', $user->id)
            ->with('package');

        if ($status) {
            $query->where('status', $status);
        }

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        // Validate sort_by
        $allowedSorts = ['created_at', 'amount', 'status', 'paid_at'];
        $sortBy = in_array($sortBy, $allowedSorts) ? $sortBy : 'created_at';
        $sortOrder = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'desc';

        $query->orderBy($sortBy, $sortOrder);
        
        // Secondary sort for consistency
        if ($sortBy !== 'created_at') {
            $query->orderBy('created_at', 'desc');
        }

        $payments = $query->paginate($perPage);

        $paymentsData = $payments->getCollection()->map(function ($payment) {
            return [
                'id' => $payment->id,
                'date' => $payment->created_at->format('Y-m-d'),
                'description' => $payment->package ? $payment->package->name : 'Payment',
                'amount' => (float) $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'package_name' => $payment->package ? $payment->package->name : null,
                'transaction_id' => $payment->transaction_id,
                'paid_at' => $payment->paid_at ? $payment->paid_at->format('Y-m-d') : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'payments' => $paymentsData,
                'pagination' => [
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                    'per_page' => $payments->perPage(),
                    'total' => $payments->total(),
                    'from' => $payments->firstItem(),
                    'to' => $payments->lastItem(),
                ],
            ],
        ]);
    }

    /**
     * Export billing history to Excel
     */
    public function export(Request $request)
    {
        $user = $request->user();
        $status = $request->get('status');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $query = Payment::where('user_id', $user->id)
            ->with('package');

        if ($status) {
            $query->where('status', $status);
        }

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        // Validate sort_by
        $allowedSorts = ['created_at', 'amount', 'status', 'paid_at'];
        $sortBy = in_array($sortBy, $allowedSorts) ? $sortBy : 'created_at';
        $sortOrder = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'desc';

        $query->orderBy($sortBy, $sortOrder);
        
        // Secondary sort for consistency
        if ($sortBy !== 'created_at') {
            $query->orderBy('created_at', 'desc');
        }

        $payments = $query->get();

        // Format data for Excel
        $exportData = $payments->map(function ($payment) {
            return [
                'Date' => $payment->created_at->format('Y-m-d'),
                'Description' => $payment->package ? $payment->package->name : 'Payment',
                'Amount' => $payment->amount,
                'Currency' => $payment->currency,
                'Status' => ucfirst($payment->status),
                'Transaction ID' => $payment->transaction_id ?? 'N/A',
                'Paid At' => $payment->paid_at ? $payment->paid_at->format('Y-m-d') : 'N/A',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'payments' => $exportData,
            ],
        ]);
    }
}

