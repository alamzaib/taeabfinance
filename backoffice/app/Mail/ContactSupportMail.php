<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Notification;

class ContactSupportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $notification;
    public $name;
    public $email;
    public $phone;
    public $subject;
    public $message;

    /**
     * Create a new message instance.
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
        $this->name = $notification->from_name ?? 'Guest';
        $this->email = $notification->from_email ?? 'noreply@taeab.com';
        $this->phone = $notification->data['phone'] ?? null;
        $this->subject = $notification->subject;
        $this->message = $notification->message;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject($this->subject)
            ->view('emails.contact-support')
            ->with([
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'subject' => $this->subject,
                'message' => $this->message,
                'to_email' => $this->notification->to_email,
            ]);
    }
}

