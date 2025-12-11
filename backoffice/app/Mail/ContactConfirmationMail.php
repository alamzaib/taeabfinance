<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Notification;

class ContactConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $notification;
    public $name;
    public $subject;

    /**
     * Create a new message instance.
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
        $this->name = $notification->from_name ?? 'Guest';
        $this->subject = 'Thank you for contacting TAEAB Support';
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject($this->subject)
            ->view('emails.contact-confirmation')
            ->with([
                'name' => $this->name,
                'subject' => $this->notification->subject,
                'notification' => $this->notification,
                'to_email' => $this->notification->to_email,
            ]);
    }
}

