<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Earning;
use App\Models\User;
use App\Models\Package;
use Illuminate\Http\Request;
use App\LogsActivity;
use Illuminate\Support\Facades\Log;

class EarningController extends Controller
{
    use LogsActivity;

    /**
     * Display earnings management
     */
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $query = Earning::with(['user', 'package'])
                ->orderBy('earned_date', 'desc')
                ->orderBy('created_at', 'desc');

            // Apply filters
            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->has('type') && $request->type) {
                $query->where('type', $request->type);
            }

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            if ($request->has('start_date') && $request->start_date) {
                $query->where('earned_date', '>=', $request->start_date);
            }

            if ($request->has('end_date') && $request->end_date) {
                $query->where('earned_date', '<=', $request->end_date);
            }

            $earnings = $query->get()->map(function ($earning) {
                return [
                    'id' => $earning->id,
                    'user_id' => $earning->user_id,
                    'user_name' => $earning->user->name ?? 'N/A',
                    'user_email' => $earning->user->email ?? 'N/A',
                    'package_id' => $earning->package_id,
                    'package_name' => $earning->package->name ?? 'N/A',
                    'type' => $earning->type,
                    'description' => $earning->description,
                    'amount' => (float) $earning->amount,
                    'currency' => $earning->currency,
                    'status' => $earning->status,
                    'earned_date' => $earning->earned_date->format('Y-m-d'),
                    'created_at' => $earning->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $earning->updated_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json($earnings->values()->all());
        }

        $users = User::orderBy('name')->get(['id', 'name', 'email']);
        $packages = Package::where('active', true)->orderBy('name')->get(['id', 'name']);
        
        return view('admin.earnings.index', compact('users', 'packages'));
    }

    /**
     * Show earning details
     */
    public function show(Earning $earning, Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $earning->load(['user', 'package']);
            return response()->json([
                'success' => true,
                'data' => [
                    'earning' => [
                        'id' => $earning->id,
                        'user_id' => $earning->user_id,
                        'user_name' => $earning->user->name ?? 'N/A',
                        'user_email' => $earning->user->email ?? 'N/A',
                        'package_id' => $earning->package_id,
                        'package_name' => $earning->package->name ?? 'N/A',
                        'type' => $earning->type,
                        'description' => $earning->description,
                        'amount' => (float) $earning->amount,
                        'currency' => $earning->currency,
                        'status' => $earning->status,
                        'earned_date' => $earning->earned_date->format('Y-m-d'),
                        'metadata' => $earning->metadata,
                        'created_at' => $earning->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $earning->updated_at->format('Y-m-d H:i:s'),
                    ]
                ]
            ]);
        }

        return view('admin.earnings.show', compact('earning'));
    }

    /**
     * Store a new earning
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'package_id' => 'nullable|exists:packages,id',
                'type' => 'required|string|in:dividend,interest,profit,bonus,referral,other',
                'description' => 'required|string|max:500',
                'amount' => 'required|numeric|min:0',
                'currency' => 'required|string|size:3',
                'status' => 'required|string|in:pending,completed,cancelled',
                'earned_date' => 'required|date',
            ]);

            $earning = Earning::create($validated);

            // Log activity
            $this->logActivity('create', 'Earnings', $earning, null, null, $validated);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Earning created successfully.',
                    'data' => ['earning' => $earning]
                ]);
            }

            return redirect()->route('earnings.index')->with('success', 'Earning created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error creating earning: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating earning: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Error creating earning.');
        }
    }

    /**
     * Edit earning (get data)
     */
    public function edit(Earning $earning, Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'earning' => [
                        'id' => $earning->id,
                        'user_id' => $earning->user_id,
                        'package_id' => $earning->package_id,
                        'type' => $earning->type,
                        'description' => $earning->description,
                        'amount' => (float) $earning->amount,
                        'currency' => $earning->currency,
                        'status' => $earning->status,
                        'earned_date' => $earning->earned_date->format('Y-m-d'),
                    ]
                ]
            ]);
        }

        return view('admin.earnings.edit', compact('earning'));
    }

    /**
     * Update earning
     */
    public function update(Request $request, Earning $earning)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'package_id' => 'nullable|exists:packages,id',
                'type' => 'required|string|in:dividend,interest,profit,bonus,referral,other',
                'description' => 'required|string|max:500',
                'amount' => 'required|numeric|min:0',
                'currency' => 'required|string|size:3',
                'status' => 'required|string|in:pending,completed,cancelled',
                'earned_date' => 'required|date',
            ]);

            $oldValues = $earning->toArray();
            $earning->update($validated);
            $newValues = $earning->fresh()->toArray();

            // Log activity
            $this->logActivity('update', 'Earnings', $earning, null, $oldValues, $newValues);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Earning updated successfully.',
                    'data' => ['earning' => $earning]
                ]);
            }

            return redirect()->route('earnings.index')->with('success', 'Earning updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating earning: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating earning: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Error updating earning.');
        }
    }

    /**
     * Delete earning
     */
    public function destroy(Earning $earning, Request $request)
    {
        try {
            $oldValues = $earning->toArray();
            
            // Log activity before deletion
            $this->logActivity('delete', 'Earnings', $earning, null, $oldValues, null);
            
            $earning->delete();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Earning deleted successfully.'
                ]);
            }

            return redirect()->route('earnings.index')->with('success', 'Earning deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting earning: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting earning: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Error deleting earning.');
        }
    }

    /**
     * Export earnings to CSV/Excel
     */
    public function export(Request $request)
    {
        try {
            $query = Earning::with(['user', 'package']);

            // Apply filters
            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->has('type') && $request->type) {
                $query->where('type', $request->type);
            }

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            if ($request->has('start_date') && $request->start_date) {
                $query->where('earned_date', '>=', $request->start_date);
            }

            if ($request->has('end_date') && $request->end_date) {
                $query->where('earned_date', '<=', $request->end_date);
            }

            $earnings = $query->orderBy('earned_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            // Format data for export
            $exportData = $earnings->map(function ($earning) {
                return [
                    'ID' => $earning->id,
                    'User Name' => $earning->user->name ?? 'N/A',
                    'User Email' => $earning->user->email ?? 'N/A',
                    'Package' => $earning->package->name ?? 'N/A',
                    'Type' => ucfirst($earning->type),
                    'Description' => $earning->description,
                    'Amount' => $earning->amount,
                    'Currency' => $earning->currency,
                    'Status' => ucfirst($earning->status),
                    'Earned Date' => $earning->earned_date->format('Y-m-d'),
                    'Created At' => $earning->created_at->format('Y-m-d H:i:s'),
                ];
            });

            if ($request->has('format') && $request->format === 'csv') {
                // Return CSV
                $filename = 'earnings_' . date('Y-m-d') . '.csv';
                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ];

                $callback = function() use ($exportData) {
                    $file = fopen('php://output', 'w');
                    
                    // Add headers
                    if ($exportData->isNotEmpty()) {
                        fputcsv($file, array_keys($exportData->first()));
                    }
                    
                    // Add data
                    foreach ($exportData as $row) {
                        fputcsv($file, $row);
                    }
                    
                    fclose($file);
                };

                return response()->stream($callback, 200, $headers);
            } else {
                // Return JSON for Excel (frontend will handle Excel conversion)
                return response()->json([
                    'success' => true,
                    'data' => [
                        'earnings' => $exportData
                    ]
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error exporting earnings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error exporting earnings: ' . $e->getMessage()
            ], 500);
        }
    }
}

