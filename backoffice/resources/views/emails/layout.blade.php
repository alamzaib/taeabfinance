<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'TAEAB')</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .email-header {
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
            padding: 30px 20px;
            text-align: center;
        }
        .email-header img {
            max-width: 150px;
            height: auto;
        }
        .email-header h1 {
            color: #ffffff;
            margin: 15px 0 0 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-body {
            padding: 40px 30px;
            color: #333333;
            line-height: 1.6;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 30px 20px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        .email-footer p {
            margin: 5px 0;
            color: #6c757d;
            font-size: 14px;
        }
        .email-footer a {
            color: #14b8a6;
            text-decoration: none;
        }
        .email-footer a:hover {
            text-decoration: underline;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #14b8a6;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: 500;
        }
        .button:hover {
            background-color: #0d9488;
        }
        .divider {
            border-top: 1px solid #e9ecef;
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            @php
                // Try to use frontend logo URL, fallback to text
                $logoUrl = config('app.frontend_url', config('app.url')) . '/images/logo.png';
            @endphp
            <img src="{{ $logoUrl }}" alt="TAEAB Logo" style="max-width: 150px; height: auto; margin-bottom: 10px;" onerror="this.style.display='none';">
            <h1>TAEAB</h1>
            <p style="color: #ffffff; margin: 5px 0 0 0; font-size: 14px;">Invest, Save, Earn</p>
        </div>

        <!-- Body -->
        <div class="email-body">
            @yield('content')
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p><strong>TAEAB</strong></p>
            <p>Your trusted partner for investments, savings, and earnings</p>
            <p>
                <a href="mailto:support@taeab.com">support@taeab.com</a> | 
                <a href="{{ config('app.url') }}">Visit Website</a>
            </p>
            <p style="margin-top: 20px; font-size: 12px; color: #adb5bd;">
                © {{ date('Y') }} TAEAB. All rights reserved.
            </p>
            <p style="font-size: 12px; color: #adb5bd;">
                This email was sent to {{ $to_email ?? 'you' }}. If you did not expect this email, please ignore it.
            </p>
            @if(isset($unsubscribeUrl) && $unsubscribeUrl)
                <p style="margin-top: 15px; font-size: 11px; color: #adb5bd;">
                    <a href="{{ $unsubscribeUrl }}" style="color: #6c757d; text-decoration: underline;">Unsubscribe from future emails</a>
                </p>
            @endif
        </div>
    </div>
</body>
</html>

