<?php

namespace App;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait LogsActivity
{
    /**
     * Log an activity
     */
    public function logActivity($action, $module, $model = null, $description = null, $oldValues = null, $newValues = null)
    {
        try {
            $user = Auth::user();
            $description = $description ?? $this->generateDescription($action, $module, $model);
            
            // Create main activity log
            ActivityLog::create([
                'user_id' => $user ? $user->id : null,
                'action' => $action,
                'module' => $module,
                'model_type' => $model ? get_class($model) : null,
                'model_id' => $model ? $model->id : null,
                'description' => $description,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'route' => Request::route() ? Request::route()->getName() : Request::path(),
                'request_data' => Request::except(['password', 'password_confirmation', '_token', '_method']),
            ]);

            // Also create specific log table entries
            if ($model) {
                $modelClass = get_class($model);
                $modelData = $model->toArray();
                
                // Remove sensitive data
                if (isset($modelData['password'])) {
                    unset($modelData['password']);
                }
                
                // Clean old/new values
                if ($oldValues && isset($oldValues['password'])) {
                    unset($oldValues['password']);
                }
                if ($newValues && isset($newValues['password'])) {
                    unset($newValues['password']);
                }

                // User Logs
                if ($modelClass === \App\Models\User::class) {
                    \App\Models\UserLog::create([
                        'user_id' => $model->id,
                        'changed_by' => $user ? $user->id : null,
                        'action' => $action,
                        'old_values' => $oldValues,
                        'new_values' => $newValues ?: $modelData,
                        'description' => $description,
                        'ip_address' => Request::ip(),
                        'user_agent' => Request::userAgent(),
                    ]);
                }
                // Payment Logs
                elseif ($modelClass === \App\Models\Payment::class) {
                    \App\Models\PaymentLog::create([
                        'payment_id' => $model->id,
                        'changed_by' => $user ? $user->id : null,
                        'action' => $action,
                        'old_values' => $oldValues,
                        'new_values' => $newValues ?: $modelData,
                        'description' => $description,
                        'ip_address' => Request::ip(),
                        'user_agent' => Request::userAgent(),
                    ]);
                }
                // Package Logs
                elseif ($modelClass === \App\Models\Package::class) {
                    \App\Models\PackageLog::create([
                        'package_id' => $model->id,
                        'changed_by' => $user ? $user->id : null,
                        'action' => $action,
                        'old_values' => $oldValues,
                        'new_values' => $newValues ?: $modelData,
                        'description' => $description,
                        'ip_address' => Request::ip(),
                        'user_agent' => Request::userAgent(),
                    ]);
                }
                // Refund Request Logs
                elseif ($modelClass === \App\Models\RefundRequest::class) {
                    \App\Models\RefundRequestLog::create([
                        'refund_request_id' => $model->id,
                        'changed_by' => $user ? $user->id : null,
                        'action' => $action,
                        'old_values' => $oldValues,
                        'new_values' => $newValues ?: $modelData,
                        'description' => $description,
                        'ip_address' => Request::ip(),
                        'user_agent' => Request::userAgent(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Log error but don't break the main flow
            \Log::error('Failed to log activity: ' . $e->getMessage(), [
                'action' => $action,
                'module' => $module,
                'exception' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Generate a description for the activity
     */
    protected function generateDescription($action, $module, $model = null)
    {
        $user = Auth::user();
        $userName = $user ? $user->name : 'System';
        
        $description = ucfirst($userName) . ' ' . $action . 'd';
        
        if ($model) {
            $modelName = class_basename($model);
            $description .= ' ' . $modelName;
            if (isset($model->name)) {
                $description .= ' "' . $model->name . '"';
            } elseif (isset($model->id)) {
                $description .= ' (ID: ' . $model->id . ')';
            }
        } else {
            $description .= ' in ' . $module;
        }
        
        return $description;
    }
}
