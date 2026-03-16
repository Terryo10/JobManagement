<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title }}</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f4f4f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .header { padding: 24px 32px; background: #1e293b; }
        .header h1 { margin: 0; color: #ffffff; font-size: 18px; font-weight: 600; }
        .body { padding: 32px; color: #374151; font-size: 15px; line-height: 1.6; }
        .body p { margin: 0 0 16px; }
        .cta { display: inline-block; margin-top: 8px; padding: 12px 24px; background: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 500; }
        .footer { padding: 20px 32px; background: #f8fafc; text-align: center; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
        </div>
        <div class="body">
            <p><strong>{{ $title }}</strong></p>
            <p>{{ $body }}</p>
            @if ($actionUrl && $actionText)
                <a href="{{ $actionUrl }}" class="cta">{{ $actionText }}</a>
            @endif
        </div>
        <div class="footer">
            You are receiving this notification because of your account settings.<br>
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
