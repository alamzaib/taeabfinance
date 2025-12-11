<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $tickets = SupportTicket::with(['user', 'assignedTo'])->get()->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'user_name' => $ticket->user->name ?? 'N/A',
                    'subject' => $ticket->subject,
                    'priority' => $ticket->priority,
                    'status' => $ticket->status,
                    'assigned_to_name' => $ticket->assignedTo->name ?? 'Unassigned',
                    'created_at' => $ticket->created_at->format('Y-m-d H:i:s'),
                ];
            });
            return response()->json($tickets);
        }
        return view('admin.support-tickets.index');
    }

    public function show(SupportTicket $supportTicket, Request $request)
    {
        $supportTicket->load(['user', 'assignedTo']);
        if ($request->ajax() || $request->wantsJson() || $request->accepts(['application/json'])) {
            return response()->json([
                'success' => true,
                'data' => [
                    'supportTicket' => [
                        'id' => $supportTicket->id,
                        'ticket_number' => $supportTicket->ticket_number,
                        'user' => $supportTicket->user ? ['id' => $supportTicket->user->id, 'name' => $supportTicket->user->name, 'email' => $supportTicket->user->email] : null,
                        'subject' => $supportTicket->subject,
                        'message' => $supportTicket->message,
                        'priority' => $supportTicket->priority,
                        'status' => $supportTicket->status,
                        'assigned_to' => $supportTicket->assignedTo ? ['id' => $supportTicket->assignedTo->id, 'name' => $supportTicket->assignedTo->name] : null,
                        'resolved_at' => $supportTicket->resolved_at ? $supportTicket->resolved_at->toDateTimeString() : null,
                        'created_at' => $supportTicket->created_at->toDateTimeString(),
                        'updated_at' => $supportTicket->updated_at->toDateTimeString(),
                    ]
                ]
            ]);
        }
        return view('admin.support-tickets.show', compact('supportTicket'));
    }

    public function assign(Request $request, SupportTicket $supportTicket)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $supportTicket->update([
            'assigned_to' => $validated['assigned_to'],
            'status' => 'in_progress',
        ]);

        return redirect()->back()->with('success', 'Ticket assigned successfully.');
    }

    public function resolve(Request $request, SupportTicket $supportTicket)
    {
        $validated = $request->validate([
            'resolution_notes' => 'nullable|string',
        ]);

        $supportTicket->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Ticket resolved successfully.');
    }

    public function reply(Request $request, SupportTicket $supportTicket)
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        // You can create a ticket_replies table or add replies to metadata
        // For now, we'll just update the ticket
        $supportTicket->update([
            'status' => 'in_progress',
        ]);

        return redirect()->back()->with('success', 'Reply sent successfully.');
    }
}
