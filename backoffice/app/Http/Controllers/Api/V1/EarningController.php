<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Earning;
use Illuminate\Http\Request;

class EarningController extends Controller
{
    /**
     * Get earnings summary and history
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get total earnings
        $totalEarnings = Earning::where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('amount');

        // Get earnings by type
        $earningsByType = Earning::where('user_id', $user->id)
            ->where('status', 'completed')
            ->selectRaw('type, SUM(amount) as total')
            ->groupBy('type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->type => (float) $item->total];
            });

        // Get monthly earnings (last 12 months)
        $monthlyEarnings = Earning::where('user_id', $user->id)
            ->where('status', 'completed')
            ->selectRaw('DATE_FORMAT(earned_date, "%Y-%m") as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get()
            ->map(function ($item) {
                return [
                    'month' => $item->month,
                    'total' => (float) $item->total,
                ];
            });

        // Get recent earnings
        $recentEarnings = Earning::where('user_id', $user->id)
            ->where('status', 'completed')
            ->with('package')
            ->orderBy('earned_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($earning) {
                return [
                    'id' => $earning->id,
                    'type' => $earning->type,
                    'description' => $earning->description,
                    'amount' => (float) $earning->amount,
                    'currency' => $earning->currency,
                    'earned_date' => $earning->earned_date->format('Y-m-d'),
                    'package_name' => $earning->package ? $earning->package->name : null,
                ];
            });

        // Get this month's earnings
        $thisMonthEarnings = Earning::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereYear('earned_date', now()->year)
            ->whereMonth('earned_date', now()->month)
            ->sum('amount');

        // Get last month's earnings
        $lastMonthEarnings = Earning::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereYear('earned_date', now()->subMonth()->year)
            ->whereMonth('earned_date', now()->subMonth()->month)
            ->sum('amount');

        // Calculate percentage change
        $percentageChange = $lastMonthEarnings > 0 
            ? (($thisMonthEarnings - $lastMonthEarnings) / $lastMonthEarnings) * 100 
            : ($thisMonthEarnings > 0 ? 100 : 0);

        return response()->json([
            'success' => true,
            'data' => [
                'total_earnings' => (float) $totalEarnings,
                'this_month_earnings' => (float) $thisMonthEarnings,
                'last_month_earnings' => (float) $lastMonthEarnings,
                'percentage_change' => round($percentageChange, 2),
                'earnings_by_type' => $earningsByType,
                'monthly_earnings' => $monthlyEarnings,
                'recent_earnings' => $recentEarnings,
            ],
        ]);
    }

    /**
     * Get earnings history with pagination
     */
    public function history(Request $request)
    {
        $user = $request->user();
        $perPage = $request->get('per_page', 20);
        $type = $request->get('type');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $sortBy = $request->get('sort_by', 'earned_date'); // earned_date, amount, type
        $sortOrder = $request->get('sort_order', 'desc'); // asc, desc

        $query = Earning::where('user_id', $user->id)
            ->where('status', 'completed')
            ->with('package');

        if ($type) {
            $query->where('type', $type);
        }

        if ($startDate) {
            $query->where('earned_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('earned_date', '<=', $endDate);
        }

        // Validate sort_by
        $allowedSorts = ['earned_date', 'amount', 'type', 'description'];
        $sortBy = in_array($sortBy, $allowedSorts) ? $sortBy : 'earned_date';
        $sortOrder = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'desc';

        $query->orderBy($sortBy, $sortOrder);
        
        // Secondary sort for consistency
        if ($sortBy !== 'earned_date') {
            $query->orderBy('earned_date', 'desc');
        }
        $query->orderBy('created_at', 'desc');

        $earnings = $query->paginate($perPage);

        $earningsData = $earnings->getCollection()->map(function ($earning) {
            return [
                'id' => $earning->id,
                'type' => $earning->type,
                'description' => $earning->description,
                'amount' => (float) $earning->amount,
                'currency' => $earning->currency,
                'earned_date' => $earning->earned_date->format('Y-m-d'),
                'package_name' => $earning->package ? $earning->package->name : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'earnings' => $earningsData,
                'pagination' => [
                    'current_page' => $earnings->currentPage(),
                    'last_page' => $earnings->lastPage(),
                    'per_page' => $earnings->perPage(),
                    'total' => $earnings->total(),
                    'from' => $earnings->firstItem(),
                    'to' => $earnings->lastItem(),
                ],
            ],
        ]);
    }

    /**
     * Export earnings to Excel
     */
    public function export(Request $request)
    {
        $user = $request->user();
        $type = $request->get('type');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = Earning::where('user_id', $user->id)
            ->where('status', 'completed')
            ->with('package');

        if ($type) {
            $query->where('type', $type);
        }

        if ($startDate) {
            $query->where('earned_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('earned_date', '<=', $endDate);
        }

        $earnings = $query->orderBy('earned_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Format data for Excel
        $exportData = $earnings->map(function ($earning) {
            return [
                'Date' => $earning->earned_date->format('Y-m-d'),
                'Type' => ucfirst($earning->type),
                'Description' => $earning->description,
                'Package' => $earning->package ? $earning->package->name : 'N/A',
                'Amount' => $earning->amount,
                'Currency' => $earning->currency,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'earnings' => $exportData,
            ],
        ]);
    }
}
