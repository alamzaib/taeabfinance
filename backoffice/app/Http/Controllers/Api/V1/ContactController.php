<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    /**
     * Store contact form submission
     */
    public function store(Request $request)
    {
        $user = $request->user(); // Can be null if not authenticated

        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'name' => $user ? 'nullable' : 'required|string|max:255',
            'email' => $user ? 'nullable' : 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Use authenticated user data if available
        $name = $user ? $user->name : $request->input('name');
        $email = $user ? $user->email : $request->input('email');
        $phone = $request->input('phone');

        // Create notification for support email
        $supportNotification = Notification::create([
            'user_id' => $user ? $user->id : null,
            'type' => 'contact_support',
            'to_email' => 'support@taeab.com',
            'from_email' => $email,
            'from_name' => $name,
            'subject' => $request->input('subject'),
            'message' => $request->input('message'),
            'data' => [
                'phone' => $phone,
            ],
            'status' => 'pending',
        ]);

        // Create notification for confirmation email to customer
        $confirmationNotification = Notification::create([
            'user_id' => $user ? $user->id : null,
            'type' => 'contact_confirmation',
            'to_email' => $email,
            'from_email' => 'support@taeab.com',
            'from_name' => 'TAEAB Support',
            'subject' => 'Thank you for contacting TAEAB Support',
            'message' => $request->input('message'), // Store original message for reference
            'data' => [
                'original_subject' => $request->input('subject'),
            ],
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your message has been received. We will get back to you soon!',
            'data' => [
                'support_notification_id' => $supportNotification->id,
                'confirmation_notification_id' => $confirmationNotification->id,
            ],
        ], 201);
    }
}

