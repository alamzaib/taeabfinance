<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Notification;

class UserRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $notification;
    public $user;
    public $unsubscribeUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
        $this->user = $notification->user;
        $this->unsubscribeUrl = $this->user && $this->user->unsubscribe_token 
            ? url('/unsubscribe/' . $this->user->unsubscribe_token)
            : null;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject($this->notification->subject)
            ->view('emails.user-registration')
            ->with([
                'user' => $this->user,
                'notification' => $this->notification,
                'to_email' => $this->notification->to_email,
                'unsubscribeUrl' => $this->unsubscribeUrl,
            ]);
    }
}

