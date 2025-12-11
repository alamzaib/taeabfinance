<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Package;
use App\Models\Payment;
use App\Models\RefundRequest;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::whereNotNull('email_verified_at')->count(),
            'total_packages' => Package::count(),
            'active_packages' => Package::where('active', true)->count(),
            'total_payments' => Payment::count(),
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            'pending_refunds' => RefundRequest::where('status', 'pending')->count(),
            'open_tickets' => SupportTicket::where('status', 'open')->count(),
        ];

        $recentPayments = Payment::with(['user', 'package'])
            ->latest()
            ->limit(10)
            ->get();

        $recentTickets = SupportTicket::with(['user', 'assignedTo'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentPayments', 'recentTickets'));
    }
}
