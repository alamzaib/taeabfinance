<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UnsubscribeController extends Controller
{
    /**
     * Handle unsubscribe request
     */
    public function unsubscribe($token)
    {
        $user = User::where('unsubscribe_token', $token)->first();

        if (!$user) {
            return view('unsubscribe.invalid');
        }

        $user->update([
            'email_notifications_enabled' => false,
        ]);

        return view('unsubscribe.success', [
            'user' => $user,
        ]);
    }

    /**
     * Resubscribe user
     */
    public function resubscribe($token)
    {
        $user = User::where('unsubscribe_token', $token)->first();

        if (!$user) {
            return view('unsubscribe.invalid');
        }

        $user->update([
            'email_notifications_enabled' => true,
        ]);

        return view('unsubscribe.resubscribed', [
            'user' => $user,
        ]);
    }
}

