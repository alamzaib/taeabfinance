<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AffiliateLink;
use App\Models\AffiliateCommission;
use App\Models\AffiliateConfig;
use Illuminate\Http\Request;

class AffiliateController extends Controller
{
    /**
     * Get user's affiliate link
     */
    public function getLink(Request $request)
    {
        $user = $request->user();
        
        $affiliateLink = AffiliateLink::firstOrCreate(
            ['user_id' => $user->id],
            function () use ($user) {
                $code = AffiliateLink::generateCode($user->id);
                $link = AffiliateLink::generateLink($code);
                return [
                    'affiliate_code' => $code,
                    'affiliate_link' => $link,
                ];
            }
        );

        return response()->json([
            'success' => true,
            'data' => [
                'affiliate_code' => $affiliateLink->affiliate_code,
                'affiliate_link' => $affiliateLink->affiliate_link,
                'clicks' => $affiliateLink->clicks,
                'signups' => $affiliateLink->signups,
            ],
        ]);
    }

    /**
     * Track affiliate link click
     */
    public function trackClick(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $affiliateLink = AffiliateLink::where('affiliate_code', $request->code)
            ->where('active', true)
            ->first();

        if ($affiliateLink) {
            $affiliateLink->increment('clicks');
            return response()->json([
                'success' => true,
                'message' => 'Click tracked',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid affiliate code',
        ], 404);
    }

    /**
     * Get user's commissions
     */
    public function getCommissions(Request $request)
    {
        $user = $request->user();

        $commissions = AffiliateCommission::where('referrer_id', $user->id)
            ->with(['referred', 'payment'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($commission) {
                return [
                    'id' => $commission->id,
                    'referred_user' => $commission->referred->name ?? 'N/A',
                    'referred_email' => $commission->referred->email ?? 'N/A',
                    'payment_id' => $commission->payment_id,
                    'payment_amount' => $commission->payment ? $commission->payment->amount : null,
                    'commission_amount' => $commission->commission_amount,
                    'commission_type' => $commission->commission_type,
                    'commission_rate' => $commission->commission_rate,
                    'status' => $commission->status,
                    'paid_at' => $commission->paid_at ? $commission->paid_at->toDateTimeString() : null,
                    'created_at' => $commission->created_at->toDateTimeString(),
                ];
            });

        // Calculate totals
        $totalEarnings = AffiliateCommission::where('referrer_id', $user->id)
            ->where('status', 'paid')
            ->sum('commission_amount');

        $pendingEarnings = AffiliateCommission::where('referrer_id', $user->id)
            ->where('status', 'pending')
            ->sum('commission_amount');

        $totalReferrals = AffiliateCommission::where('referrer_id', $user->id)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'commissions' => $commissions,
                'summary' => [
                    'total_earnings' => $totalEarnings,
                    'pending_earnings' => $pendingEarnings,
                    'total_referrals' => $totalReferrals,
                ],
            ],
        ]);
    }

    /**
     * Get affiliate statistics
     */
    public function getStats(Request $request)
    {
        $user = $request->user();
        
        $affiliateLink = AffiliateLink::where('user_id', $user->id)->first();
        
        if (!$affiliateLink) {
            return response()->json([
                'success' => false,
                'message' => 'Affiliate link not found',
            ], 404);
        }

        $totalCommissions = AffiliateCommission::where('referrer_id', $user->id)
            ->where('status', 'paid')
            ->sum('commission_amount');

        $pendingCommissions = AffiliateCommission::where('referrer_id', $user->id)
            ->where('status', 'pending')
            ->sum('commission_amount');

        return response()->json([
            'success' => true,
            'data' => [
                'clicks' => $affiliateLink->clicks,
                'signups' => $affiliateLink->signups,
                'total_earnings' => $totalCommissions,
                'pending_earnings' => $pendingCommissions,
                'conversion_rate' => $affiliateLink->clicks > 0 
                    ? round(($affiliateLink->signups / $affiliateLink->clicks) * 100, 2) 
                    : 0,
            ],
        ]);
    }
}
