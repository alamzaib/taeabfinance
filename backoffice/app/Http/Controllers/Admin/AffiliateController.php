<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateLink;
use App\Models\AffiliateCommission;
use App\Models\AffiliateConfig;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\LogsActivity;

class AffiliateController extends Controller
{
    use LogsActivity;

    /**
     * Display affiliate links management
     */
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            try {
                // Get all users
                $users = User::orderBy('name')->get();

                $links = $users->map(function ($user) {
                    // Get or create affiliate link for each user
                    $affiliateLink = AffiliateLink::firstOrNew(['user_id' => $user->id]);

                    // If it's a new link, generate code and link
                    if (!$affiliateLink->exists) {
                        $affiliateLink->affiliate_code = AffiliateLink::generateCode($user->id);
                        $affiliateLink->affiliate_link = AffiliateLink::generateLink($affiliateLink->affiliate_code);
                        $affiliateLink->clicks = 0;
                        $affiliateLink->signups = 0;
                        $affiliateLink->active = true;
                        $affiliateLink->save();
                    }

                    return [
                        'id' => $affiliateLink->id,
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'user_email' => $user->email,
                        'affiliate_code' => $affiliateLink->affiliate_code,
                        'affiliate_link' => $affiliateLink->affiliate_link,
                        'clicks' => $affiliateLink->clicks ?? 0,
                        'signups' => $affiliateLink->signups ?? 0,
                        'active' => $affiliateLink->active ?? true,
                        'created_at' => $affiliateLink->created_at ? $affiliateLink->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                    ];
                });

                return response()->json($links->values());
            } catch (\Exception $e) {
                Log::error('Error loading affiliate links: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'error' => 'Error loading affiliate links: ' . $e->getMessage()
                ], 500);
            }
        }

        return view('admin.affiliates.index');
    }

    /**
     * Display commissions
     */
    public function commissions(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $query = AffiliateCommission::with(['referrer', 'referred', 'payment'])
                ->orderBy('created_at', 'desc');

            // Apply filters
            if ($request->has('referrer_id') && $request->referrer_id) {
                $query->where('referrer_id', $request->referrer_id);
            }

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            $commissions = $query->get()->map(function ($commission) {
                return [
                    'id' => $commission->id,
                    'referrer_name' => $commission->referrer->name ?? 'N/A',
                    'referrer_email' => $commission->referrer->email ?? 'N/A',
                    'referred_name' => $commission->referred->name ?? 'N/A',
                    'referred_email' => $commission->referred->email ?? 'N/A',
                    'payment_id' => $commission->payment_id,
                    'commission_type_display' => $commission->payment_id ? 'Payment Commission' : 'Signup Commission',
                    'commission_amount' => (float) $commission->commission_amount,
                    'commission_type' => $commission->commission_type,
                    'commission_rate' => $commission->commission_rate ? (float) $commission->commission_rate : null,
                    'status' => $commission->status,
                    'paid_at' => $commission->paid_at ? $commission->paid_at->format('Y-m-d H:i:s') : null,
                    'created_at' => $commission->created_at->format('Y-m-d H:i:s'),
                ];
            });

            // Return array directly for Tabulator
            return response()->json($commissions->values()->all());
        }

        $users = User::orderBy('name')->get(['id', 'name', 'email']);
        return view('admin.affiliates.commissions', compact('users'));
    }

    /**
     * Display configuration
     */
    public function config()
    {
        $configs = AffiliateConfig::all()->pluck('value', 'key')->toArray();
        return view('admin.affiliates.config', compact('configs'));
    }

    /**
     * Update configuration
     */
    public function updateConfig(Request $request)
    {
        $commissionType = $request->input('commission_type');

        // Conditional validation based on commission type
        $rules = [
            'commission_type' => 'required|string|in:percentage,fixed',
            'signup_bonus_amount' => 'nullable|numeric|min:0',
            'min_payment_for_commission' => 'nullable|numeric|min:0',
        ];

        if ($commissionType === 'percentage') {
            $rules['commission_rate'] = 'required|numeric|min:0|max:100';
        } else if ($commissionType === 'fixed') {
            $rules['fixed_commission_amount'] = 'required|numeric|min:0';
        }

        $validated = $request->validate($rules);

        // Save commission type
        AffiliateConfig::setValue('commission_type', $validated['commission_type'], 'Commission type: percentage or fixed');

        // Save commission rate or fixed amount based on type
        if ($commissionType === 'percentage') {
            AffiliateConfig::setValue('commission_rate', $validated['commission_rate'], 'Commission rate percentage');
            // Clear fixed amount if switching to percentage
            AffiliateConfig::where('key', 'fixed_commission_amount')->delete();
        } else if ($commissionType === 'fixed') {
            AffiliateConfig::setValue('fixed_commission_amount', $validated['fixed_commission_amount'], 'Fixed commission amount');
            // Clear commission rate if switching to fixed
            AffiliateConfig::where('key', 'commission_rate')->delete();
        }

        // Save signup bonus amount
        if (isset($validated['signup_bonus_amount'])) {
            AffiliateConfig::setValue('signup_bonus_amount', $validated['signup_bonus_amount'], 'Fixed commission amount for user signup');
        }

        // Save minimum payment threshold
        if (isset($validated['min_payment_for_commission'])) {
            AffiliateConfig::setValue('min_payment_for_commission', $validated['min_payment_for_commission'], 'Minimum payment amount to generate commission');
        }

        $this->logActivity('update', 'Affiliate Config', null, 'Affiliate configuration updated');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Configuration updated successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Configuration updated successfully.');
    }

    /**
     * Update commission status
     */
    public function updateCommissionStatus(Request $request, AffiliateCommission $commission)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,approved,paid,cancelled',
        ]);

        $oldValues = $commission->toArray();
        $commission->update([
            'status' => $validated['status'],
            'paid_at' => $validated['status'] === 'paid' ? now() : null,
        ]);
        $newValues = $commission->fresh()->toArray();

        $this->logActivity('update', 'Affiliate Commissions', $commission, null, $oldValues, $newValues);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Commission status updated successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Commission status updated successfully.');
    }

    /**
     * Toggle affiliate link status
     */
    public function toggleLinkStatus(Request $request, AffiliateLink $affiliateLink)
    {
        $oldValues = $affiliateLink->toArray();
        $affiliateLink->update(['active' => !$affiliateLink->active]);
        $newValues = $affiliateLink->fresh()->toArray();

        $this->logActivity('update', 'Affiliate Links', $affiliateLink, null, $oldValues, $newValues);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Affiliate link status updated successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Affiliate link status updated successfully.');
    }
}
