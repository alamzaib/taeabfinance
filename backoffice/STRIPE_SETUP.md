# Stripe Payment Integration Setup Guide

## Overview

This guide explains how to set up and configure the Stripe payment integration for the TAEAB application.

## Features

-   Admin can review package requests from users
-   Admin can generate Stripe payment links for pending payments
-   Automatic payment status updates via Stripe webhooks
-   Payment links displayed in user billing section
-   Payment status tracking (pending, completed, failed)

## Prerequisites

1. Stripe account (sign up at https://stripe.com)
2. Stripe API keys (available in Stripe Dashboard)

## Installation Steps

### 1. Install Stripe PHP SDK

The Stripe PHP SDK is already added to `composer.json`. Run:

```bash
cd backoffice
composer install
```

### 2. Run Database Migration

Add Stripe fields to payments table:

```bash
php artisan migrate
```

This will add the following fields to the `payments` table:

-   `stripe_session_id` - Stripe Checkout Session ID
-   `stripe_payment_intent_id` - Stripe Payment Intent ID
-   `payment_link` - Public payment link URL
-   `stripe_customer_id` - Stripe Customer ID

### 3. Configure Environment Variables

Add the following to your `.env` file:

```env
# Stripe Configuration
# Replace YOUR_PUBLISHABLE_KEY with your actual Stripe publishable key from Stripe Dashboard
STRIPE_KEY=YOUR_PUBLISHABLE_KEY
# Replace YOUR_SECRET_KEY with your actual Stripe secret key from Stripe Dashboard
STRIPE_SECRET=YOUR_SECRET_KEY
# Replace YOUR_WEBHOOK_SECRET with your webhook signing secret (see step 4)
STRIPE_WEBHOOK_SECRET=YOUR_WEBHOOK_SECRET
```

**Where to find these values:**

-   **STRIPE_KEY** (Publishable Key): Stripe Dashboard → Developers → API keys → Publishable key
-   **STRIPE_SECRET** (Secret Key): Stripe Dashboard → Developers → API keys → Secret key
-   **STRIPE_WEBHOOK_SECRET**: After setting up webhook endpoint (see step 4)

### 4. Set Up Stripe Webhook

#### For Local Development (using Stripe CLI):

1. Install Stripe CLI: https://stripe.com/docs/stripe-cli
2. Login: `stripe login`
3. Forward webhooks to local server:
    ```bash
    stripe listen --forward-to http://localhost:8000/api/v1/stripe/webhook
    ```
4. Copy the webhook signing secret (starts with `whsec_`) and add it to `.env` as `STRIPE_WEBHOOK_SECRET`

#### For Production:

1. Go to Stripe Dashboard → Developers → Webhooks
2. Click "Add endpoint"
3. Enter your webhook URL: `https://yourdomain.com/api/v1/stripe/webhook`
4. Select events to listen to:
    - `checkout.session.completed`
    - `payment_intent.succeeded`
    - `payment_intent.payment_failed`
5. Copy the webhook signing secret and add it to `.env`

### 5. Update App URL

Make sure your `APP_URL` in `.env` is set correctly:

```env
APP_URL=http://localhost:8000
```

For production:

```env
APP_URL=https://yourdomain.com
```

## Usage

### Admin Workflow

1. **Review Package Requests**

    - Navigate to `/backoffice/payments`
    - View pending payment requests from users
    - Double-click on a payment to view details

2. **Generate Payment Link**

    - Open payment details modal
    - Click "Generate Payment Link" button
    - Copy the generated link and share it with the user
    - The link will redirect users to Stripe Checkout

3. **Monitor Payment Status**
    - Payment status updates automatically via webhooks
    - Completed payments show in green
    - Failed payments show in red

### User Workflow

1. **Request Package**

    - User selects a package on `/packages` page
    - Clicks "Request Approval"
    - Payment request is created with "pending" status

2. **Receive Payment Link**

    - Admin generates payment link
    - User receives the link (via email or admin shares it)

3. **Complete Payment**
    - User clicks "Pay Now" button in `/billing` page
    - Redirected to Stripe Checkout
    - Completes payment
    - Automatically redirected back to success page
    - Payment status updates to "completed"

## API Endpoints

### Admin Endpoints (Protected)

-   `POST /backoffice/payments/{payment}/generate-payment-link` - Generate Stripe payment link
-   `GET /backoffice/payments/{payment}/payment-status` - Get payment status

### Public Endpoints

-   `GET /payment/{token}` - View payment page (redirects to Stripe if link exists)
-   `GET /payment/success` - Payment success page
-   `GET /payment/cancel` - Payment cancel page

### Webhook Endpoint

-   `POST /api/v1/stripe/webhook` - Stripe webhook handler (no authentication)

## Testing

### Test Mode

Use Stripe test mode keys for development. Test keys start with `pk_test_` (publishable) and `sk_test_` (secret).

### Test Cards

Use these test card numbers in Stripe Checkout:

-   **Success**: `4242 4242 4242 4242`
-   **Decline**: `4000 0000 0000 0002`
-   **3D Secure**: `4000 0025 0000 3155`

Use any future expiry date, any 3-digit CVC, and any postal code.

## Troubleshooting

### Payment link not generating

-   Check Stripe API keys are correct in `.env`
-   Verify Stripe account is active
-   Check Laravel logs for errors

### Webhook not working

-   Verify webhook secret is correct
-   Check webhook endpoint URL is accessible
-   Ensure webhook events are selected in Stripe Dashboard
-   Check Laravel logs for webhook errors

### Payment status not updating

-   Verify webhook is configured correctly
-   Check webhook events are being received (Stripe Dashboard → Webhooks → View logs)
-   Ensure webhook secret matches in `.env`

## Security Notes

1. **Never commit `.env` file** with real Stripe keys
2. **Use environment variables** for all sensitive data
3. **Enable webhook signature verification** (already implemented)
4. **Use HTTPS in production** for webhook endpoints
5. **Rotate API keys** if compromised

## Support

For Stripe-specific issues, refer to:

-   Stripe Documentation: https://stripe.com/docs
-   Stripe Support: https://support.stripe.com

For application-specific issues, check:

-   Laravel logs: `storage/logs/laravel.log`
-   Payment records in database: `payments` table
