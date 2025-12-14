<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Notification;

class ReferralSignupMail extends Mailable
{
    use Queueable, SerializesModels;

    public $notification;
    public $referrer;
    public $newUser;
    public $unsubscribeUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
        $this->referrer = $notification->user;
        $this->newUser = isset($notification->data['new_user']) 
            ? (object) $notification->data['new_user'] 
            : null;
        $this->unsubscribeUrl = $this->referrer && $this->referrer->unsubscribe_token 
            ? url('/unsubscribe/' . $this->referrer->unsubscribe_token)
            : null;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject($this->notification->subject)
            ->view('emails.referral-signup')
            ->with([
                'referrer' => $this->referrer,
                'newUser' => $this->newUser,
                'notification' => $this->notification,
                'to_email' => $this->notification->to_email,
                'unsubscribeUrl' => $this->unsubscribeUrl,
            ]);
    }
}

