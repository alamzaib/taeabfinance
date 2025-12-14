<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\LogsActivity;

class UserController extends Controller
{
    use LogsActivity;
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $users = User::with('roles')->get()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name')->map(fn($name) => '<span class="badge badge-info">' . $name . '</span>')->join(' '),
                    'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                ];
            });
            return response()->json($users);
        }

        $roles = Role::all();
        return view('admin.users.index', compact('roles'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'roles' => 'array',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            if ($request->has('roles')) {
                $user->assignRole($request->roles);
            }
            
            // Log activity
            $userData = $user->toArray();
            unset($userData['password']); // Don't log password
            $this->logActivity('create', 'Users', $user, null, null, $userData);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'User created successfully.']);
            }

            return redirect()->route('users.index')->with('success', 'User created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }
    }

    public function show(User $user)
    {
        $user->load('roles', 'permissions');

        if (request()->ajax() || request()->wantsJson() || request()->accepts(['application/json'])) {
            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'roles' => $user->roles->map(function ($role) {
                            return ['id' => $role->id, 'name' => $role->name];
                        }),
                        'permissions' => $user->permissions->pluck('name'),
                        'created_at' => $user->created_at->toISOString(),
                        'updated_at' => $user->updated_at->toISOString(),
                    ],
                ],
            ]);
        }

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $user->load('roles');
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'password' => 'nullable|string|min:8|confirmed',
                'roles' => 'array',
            ]);

            // Get old values before update
            $oldValues = $user->toArray();
            unset($oldValues['password']); // Don't log password
            
            // Track role changes
            $oldRoles = $user->roles->pluck('name')->toArray();

            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            if ($request->filled('password')) {
                $user->update(['password' => Hash::make($validated['password'])]);
            }

            if ($request->has('roles')) {
                $user->syncRoles($request->roles);
            }
            
            // Get new values after update
            $user->refresh();
            $newValues = $user->toArray();
            unset($newValues['password']); // Don't log password
            
            // Add role information
            $newRoles = $user->roles->pluck('name')->toArray();
            $oldValues['roles'] = $oldRoles;
            $newValues['roles'] = $newRoles;
            
            // Log activity
            $this->logActivity('update', 'Users', $user, null, $oldValues, $newValues);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'User updated successfully.']);
            }

            return redirect()->route('users.index')->with('success', 'User updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }
    }

    public function destroy(User $user)
    {
        // Get old values before deletion
        $oldValues = $user->toArray();
        unset($oldValues['password']); // Don't log password
        $oldValues['roles'] = $user->roles->pluck('name')->toArray();
        
        // Log activity before deletion
        $this->logActivity('delete', 'Users', $user, null, $oldValues, null);
        
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function toggleStatus(User $user)
    {
        // You can implement email verification toggle or active status
        return response()->json(['success' => true]);
    }
}
