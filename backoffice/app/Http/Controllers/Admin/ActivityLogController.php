<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $query = ActivityLog::with('user')
                ->orderBy('created_at', 'desc');

            // Apply filters
            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->has('action') && $request->action) {
                $query->where('action', $request->action);
            }

            if ($request->has('module') && $request->module) {
                $query->where('module', $request->module);
            }

            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Return all filtered data - Tabulator will handle pagination client-side
            $logs = $query->get();
            $logsData = $logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'user_name' => $log->user ? $log->user->name : 'System',
                    'user_email' => $log->user ? $log->user->email : null,
                    'action' => $log->action,
                    'module' => $log->module,
                    'model_type' => $log->model_type ? class_basename($log->model_type) : null,
                    'model_id' => $log->model_id,
                    'description' => $log->description,
                    'ip_address' => $log->ip_address,
                    'route' => $log->route,
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                    'created_at_formatted' => $log->created_at->format('M d, Y H:i'),
                ];
            });
            return response()->json($logsData);
        }

        // Get filter options for dropdowns
        $users = DB::table('users')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        $actions = ActivityLog::distinct()
            ->pluck('action')
            ->sort()
            ->values();

        $modules = ActivityLog::distinct()
            ->pluck('module')
            ->sort()
            ->values();

        return view('admin.activity-logs.index', compact('users', 'actions', 'modules'));
    }

    public function show(ActivityLog $activityLog)
    {
        $activityLog->load('user');
        
        if (request()->ajax() || request()->wantsJson() || request()->accepts(['application/json'])) {
            return response()->json([
                'success' => true,
                'data' => [
                    'log' => [
                        'id' => $activityLog->id,
                        'user' => $activityLog->user ? [
                            'id' => $activityLog->user->id,
                            'name' => $activityLog->user->name,
                            'email' => $activityLog->user->email,
                        ] : null,
                        'action' => $activityLog->action,
                        'module' => $activityLog->module,
                        'model_type' => $activityLog->model_type,
                        'model_id' => $activityLog->model_id,
                        'description' => $activityLog->description,
                        'old_values' => $activityLog->old_values,
                        'new_values' => $activityLog->new_values,
                        'ip_address' => $activityLog->ip_address,
                        'user_agent' => $activityLog->user_agent,
                        'route' => $activityLog->route,
                        'request_data' => $activityLog->request_data,
                        'created_at' => $activityLog->created_at->toDateTimeString(),
                    ],
                ],
            ]);
        }

        return view('admin.activity-logs.show', compact('activityLog'));
    }

    public function export(Request $request)
    {
        $query = ActivityLog::with('user')
            ->orderBy('created_at', 'desc');

        // Apply same filters as index
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('action') && $request->action) {
            $query->where('action', $request->action);
        }

        if ($request->has('module') && $request->module) {
            $query->where('module', $request->module);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->get();

        $data = $logs->map(function ($log) {
            return [
                'ID' => $log->id,
                'User' => $log->user ? $log->user->name : 'System',
                'Email' => $log->user ? $log->user->email : '',
                'Action' => ucfirst($log->action),
                'Module' => $log->module,
                'Model Type' => $log->model_type ? class_basename($log->model_type) : '',
                'Model ID' => $log->model_id ?? '',
                'Description' => $log->description,
                'IP Address' => $log->ip_address,
                'Route' => $log->route,
                'Created At' => $log->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
