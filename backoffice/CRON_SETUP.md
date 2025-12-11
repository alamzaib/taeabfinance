# Cron Job Setup for Email Notifications

This document explains how to set up the automated email notification system.

## Overview

The system uses Laravel's task scheduler to send emails from the `notifications` table in batches. The cron job runs every 2 minutes and sends up to 10 pending emails at a time.

## Setup Instructions

### 1. Run the Migration

First, create the notifications table:

```bash
cd backoffice
php artisan migrate
```

### 2. Configure Laravel Scheduler

Add the following cron entry to your server's crontab. This will run Laravel's scheduler every minute:

```bash
* * * * * cd /path/to/backoffice && php artisan schedule:run >> /dev/null 2>&1
```

**For Windows/XAMPP:**

If you're using XAMPP on Windows, you can use Task Scheduler or run the scheduler manually:

```bash
# Run this command manually or set up a Windows Task Scheduler task
php artisan schedule:run
```

**For Production (Linux):**

```bash
# Edit crontab
crontab -e

# Add this line (replace /path/to/backoffice with your actual path)
* * * * * cd /path/to/backoffice && php artisan schedule:run >> /dev/null 2>&1
```

### 3. Test the Command

You can manually test the notification sending command:

```bash
php artisan notifications:send --limit=10
```

This will send up to 10 pending notifications immediately.

### 4. Configure Mail Settings

Make sure your `.env` file has the correct mail configuration:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=support@taeab.com
MAIL_FROM_NAME="${APP_NAME}"
```

## How It Works

1. **Contact Form Submission**: When a user submits the contact form, two notifications are created:
   - One for support@taeab.com (support email)
   - One for the customer (confirmation email)

2. **Notifications Table**: All emails are stored in the `notifications` table with status `pending`.

3. **Cron Job**: Every 2 minutes, the scheduler runs `notifications:send --limit=10` which:
   - Fetches up to 10 pending notifications
   - Sends each email using the appropriate Mail class
   - Updates the notification status to `sent` or increments `attempts` on failure

4. **Email Templates**: Professional email templates with header/footer are located in:
   - `resources/views/emails/layout.blade.php` (base template)
   - `resources/views/emails/contact-support.blade.php` (support email)
   - `resources/views/emails/contact-confirmation.blade.php` (confirmation email)

## Monitoring

You can check the status of notifications:

```bash
# View pending notifications
php artisan tinker
>>> App\Models\Notification::pending()->count();

# View failed notifications
>>> App\Models\Notification::where('status', 'failed')->get();
```

## Troubleshooting

### Emails not sending?

1. Check mail configuration in `.env`
2. Verify cron job is running: `php artisan schedule:list`
3. Check logs: `storage/logs/laravel.log`
4. Test manually: `php artisan notifications:send --limit=1`

### Too many emails queued?

Increase the limit in `routes/console.php`:

```php
Schedule::command('notifications:send --limit=20')
    ->everyTwoMinutes()
```

Or run the command more frequently:

```php
Schedule::command('notifications:send --limit=10')
    ->everyMinute()
```

## API Endpoint

The contact form uses the following endpoint:

```
POST /api/v1/contact
```

**Request Body:**
```json
{
  "subject": "Support Request",
  "message": "I need help with...",
  "name": "John Doe",      // Optional if user is logged in
  "email": "john@example.com", // Optional if user is logged in
  "phone": "+1234567890"   // Optional
}
```

**Response:**
```json
{
  "success": true,
  "message": "Your message has been received. We will get back to you soon!",
  "data": {
    "support_notification_id": 1,
    "confirmation_notification_id": 2
  }
}
```

