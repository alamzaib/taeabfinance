<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $roles = Role::withCount('users')->with('permissions')->get()->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions' => $role->permissions->pluck('name')->map(fn($name) => '<span class="badge badge-info">' . $name . '</span>')->join(' '),
                    'users_count' => $role->users_count ?? 0,
                    'created_at' => $role->created_at->format('Y-m-d H:i:s'),
                ];
            });
            return response()->json($roles);
        }
        return view('admin.roles.index');
    }

    public function create()
    {
        $permissions = $this->organizePermissionsByModule();
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array',
        ]);

        $role = Role::create(['name' => $validated['name']]);

        if ($request->has('permissions')) {
            $role->givePermissionTo($request->permissions);
        }

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function show(Role $role, Request $request)
    {
        $role->load(['permissions', 'users']);
        if ($request->ajax() || $request->wantsJson() || $request->accepts(['application/json'])) {
            return response()->json([
                'success' => true,
                'data' => [
                    'role' => [
                        'id' => $role->id,
                        'name' => $role->name,
                        'permissions' => $role->permissions->map(function ($permission) {
                            return ['id' => $permission->id, 'name' => $permission->name];
                        }),
                        'users_count' => $role->users->count(),
                        'users' => $role->users->map(function ($user) {
                            return ['id' => $user->id, 'name' => $user->name, 'email' => $user->email];
                        }),
                        'created_at' => $role->created_at->toDateTimeString(),
                        'updated_at' => $role->updated_at->toDateTimeString(),
                    ]
                ]
            ]);
        }
        return view('admin.roles.show', compact('role'));
    }

    public function edit(Role $role)
    {
        $permissions = $this->organizePermissionsByModule();
        $role->load('permissions');
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'array',
        ]);

        $role->update(['name' => $validated['name']]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }

    public function syncPermissions(Request $request, Role $role)
    {
        $role->syncPermissions($request->permissions ?? []);
        return response()->json(['success' => true, 'message' => 'Permissions updated successfully.']);
    }

    /**
     * Organize permissions by module (e.g., users, packages, etc.)
     * Returns array with module name as key and permissions grouped by action
     */
    private function organizePermissionsByModule()
    {
        $allPermissions = Permission::all();
        $organized = [];

        // Define module display names
        $moduleNames = [
            'users' => 'Users',
            'roles' => 'Roles',
            'packages' => 'Packages',
            'payments' => 'Payments',
            'refunds' => 'Refunds',
            'support-tickets' => 'Support Tickets',
            'logs' => 'Logs',
            'analytics' => 'Analytics',
        ];

        // Define action display names
        $actionNames = [
            'read' => 'Read',
            'write' => 'Write',
            'update' => 'Update',
            'delete' => 'Delete',
        ];

        // Group permissions by module
        foreach ($allPermissions as $permission) {
            // Check if permission follows module.action pattern
            if (strpos($permission->name, '.') !== false) {
                list($module, $action) = explode('.', $permission->name, 2);
                
                if (!isset($organized[$module])) {
                    $organized[$module] = [
                        'name' => $moduleNames[$module] ?? ucfirst($module),
                        'permissions' => []
                    ];
                }
                
                $organized[$module]['permissions'][$action] = [
                    'permission' => $permission,
                    'name' => $actionNames[$action] ?? ucfirst($action),
                ];
            } else {
                // Handle legacy permissions that don't follow the pattern
                if (!isset($organized['other'])) {
                    $organized['other'] = [
                        'name' => 'Other',
                        'permissions' => []
                    ];
                }
                $organized['other']['permissions'][$permission->name] = [
                    'permission' => $permission,
                    'name' => $permission->name,
                ];
            }
        }

        // Sort modules
        ksort($organized);

        return $organized;
    }
}
