<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use App\LogsActivity;

class PackageController extends Controller
{
    use LogsActivity;
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $packages = Package::all()->map(function ($package) {
                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'price' => $package->price,
                    'currency' => $package->currency,
                    'period' => $package->period,
                    'popular' => $package->popular,
                    'active' => $package->active,
                    'created_at' => $package->created_at->format('Y-m-d H:i:s'),
                ];
            });
            return response()->json($packages);
        }
        return view('admin.packages.index');
    }

    public function create()
    {
        // Return empty response for AJAX requests (form is in modal)
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return view('admin.packages.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'currency' => 'required|string|size:3',
                'period' => 'required|string|in:month,year',
                'features' => 'nullable|array',
                'popular' => 'boolean',
                'active' => 'boolean',
            ]);

            $package = Package::create($validated);
            
            // Log activity
            $this->logActivity('create', 'Packages', $package, null, null, $validated);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Package created successfully.']);
            }

            return redirect()->route('packages.index')->with('success', 'Package created successfully.');
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

    public function show(Package $package, Request $request)
    {
        if ($request->ajax() || $request->wantsJson() || $request->accepts(['application/json'])) {
            return response()->json([
                'success' => true,
                'data' => [
                    'package' => [
                        'id' => $package->id,
                        'name' => $package->name,
                        'price' => $package->price,
                        'currency' => $package->currency,
                        'period' => $package->period,
                        'features' => $package->features ?? [],
                        'popular' => $package->popular,
                        'active' => $package->active,
                        'created_at' => $package->created_at->toDateTimeString(),
                        'updated_at' => $package->updated_at->toDateTimeString(),
                    ]
                ]
            ]);
        }
        return view('admin.packages.show', compact('package'));
    }

    public function edit(Package $package, Request $request)
    {
        if ($request->ajax() || $request->wantsJson() || $request->accepts(['application/json'])) {
            return response()->json([
                'success' => true,
                'data' => [
                    'package' => [
                        'id' => $package->id,
                        'name' => $package->name,
                        'price' => $package->price,
                        'currency' => $package->currency,
                        'period' => $package->period,
                        'features' => $package->features ?? [],
                        'popular' => $package->popular,
                        'active' => $package->active,
                    ]
                ]
            ]);
        }
        return view('admin.packages.edit', compact('package'));
    }

    public function update(Request $request, Package $package)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'currency' => 'required|string|size:3',
                'period' => 'required|string|in:month,year',
                'features' => 'nullable|array',
                'popular' => 'boolean',
                'active' => 'boolean',
            ]);

            $oldValues = $package->toArray();
            $package->update($validated);
            $newValues = $package->fresh()->toArray();
            
            // Log activity
            $this->logActivity('update', 'Packages', $package, null, $oldValues, $newValues);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Package updated successfully.']);
            }

            return redirect()->route('packages.index')->with('success', 'Package updated successfully.');
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

    public function destroy(Package $package, Request $request)
    {
        try {
            $oldValues = $package->toArray();
            
            // Log activity before deletion
            $this->logActivity('delete', 'Packages', $package, null, $oldValues, null);
            
            $package->delete();
            
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Package deleted successfully.'
                ]);
            }
            
            return redirect()->route('packages.index')->with('success', 'Package deleted successfully.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting package: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('packages.index')->with('error', 'Error deleting package.');
        }
    }

    public function toggleStatus(Package $package)
    {
        $package->update(['active' => !$package->active]);
        return response()->json(['success' => true, 'active' => $package->active]);
    }
}
