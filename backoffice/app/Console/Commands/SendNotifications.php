<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Mail\ContactSupportMail;
use App\Mail\ContactConfirmationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send {--limit=10 : Number of notifications to send}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send pending notifications in batches';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $notifications = Notification::readyToSend()
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        if ($notifications->isEmpty()) {
            $this->info('No pending notifications to send.');
            return 0;
        }

        $this->info("Processing {$notifications->count()} notification(s)...");

        $sent = 0;
        $failed = 0;

        foreach ($notifications as $notification) {
            try {
                $this->sendNotification($notification);
                $sent++;
                $this->info("✓ Sent notification #{$notification->id} to {$notification->to_email}");
            } catch (\Exception $e) {
                $failed++;
                $notification->increment('attempts');
                $notification->update([
                    'error_message' => $e->getMessage(),
                ]);
                $this->error("✗ Failed to send notification #{$notification->id}: {$e->getMessage()}");
                Log::error("Failed to send notification #{$notification->id}", [
                    'error' => $e->getMessage(),
                    'notification' => $notification->toArray(),
                ]);
            }
        }

        $this->info("\nCompleted: {$sent} sent, {$failed} failed");

        return 0;
    }

    /**
     * Send a notification email
     */
    protected function sendNotification(Notification $notification)
    {
        $mailable = null;

        switch ($notification->type) {
            case 'contact_support':
                $mailable = new ContactSupportMail($notification);
                break;
            case 'contact_confirmation':
                $mailable = new ContactConfirmationMail($notification);
                break;
            default:
                throw new \Exception("Unknown notification type: {$notification->type}");
        }

        if ($mailable) {
            Mail::to($notification->to_email)->send($mailable);
            
            $notification->update([
                'status' => 'sent',
                'sent_at' => now(),
                'attempts' => $notification->attempts + 1,
            ]);
        }
    }
}

