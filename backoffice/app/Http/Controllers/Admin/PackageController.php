<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
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

            Package::create($validated);

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

            $package->update($validated);

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

    public function destroy(Package $package)
    {
        $package->delete();
        return redirect()->route('packages.index')->with('success', 'Package deleted successfully.');
    }

    public function toggleStatus(Package $package)
    {
        $package->update(['active' => !$package->active]);
        return response()->json(['success' => true, 'active' => $package->active]);
    }
}
