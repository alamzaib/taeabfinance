<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribed - TAEAB</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 500px;
            text-align: center;
        }
        h1 {
            color: #14b8a6;
            margin-bottom: 20px;
        }
        p {
            color: #333;
            line-height: 1.6;
            margin: 15px 0;
        }
        .success-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        a {
            color: #14b8a6;
            text-decoration: none;
            font-weight: 500;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">✓</div>
        <h1>Successfully Unsubscribed</h1>
        <p>You have been unsubscribed from future email notifications from TAEAB.</p>
        <p>You will no longer receive promotional emails, but you may still receive important account-related communications.</p>
        <p style="margin-top: 30px;">
            <a href="{{ route('resubscribe', $user->unsubscribe_token) }}">Click here to resubscribe</a>
        </p>
        <p style="margin-top: 20px;">
            <a href="{{ config('app.frontend_url', config('app.url')) }}">Return to Website</a>
        </p>
    </div>
</body>
</html>

