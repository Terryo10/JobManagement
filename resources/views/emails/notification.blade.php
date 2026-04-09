<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title }}</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f4f4f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .header { padding: 24px 32px; background: #1e293b; display: flex; align-items: center; gap: 12px; }
        .header h1 { margin: 0; color: #ffffff; font-size: 18px; font-weight: 600; }
        .type-badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; letter-spacing: .4px; text-transform: uppercase; margin: 0 32px 0; }
        .badge-info    { background: #dbeafe; color: #1d4ed8; }
        .badge-warning { background: #fef9c3; color: #a16207; }
        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-danger  { background: #fee2e2; color: #b91c1c; }
        .body { padding: 28px 32px 24px; color: #374151; font-size: 15px; line-height: 1.6; }
        .body p { margin: 0 0 16px; }
        .cta { display: inline-block; margin-top: 8px; padding: 12px 24px; background: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 500; }
        .footer { padding: 20px 32px; background: #f8fafc; border-top: 1px solid #e5e7eb; font-size: 12px; color: #9ca3af; line-height: 1.6; }
        .footer a { color: #6b7280; }
        /* Gmail pre-header hack */
        .preheader { display: none; max-height: 0; overflow: hidden; font-size: 1px; line-height: 1px; color: transparent; }
    </style>
</head>
<body>
    {{-- Pre-header: shows as grey preview text in Gmail/Outlook before the email is opened --}}
    <span class="preheader">{{ $typeLabel ? $typeLabel . ': ' : '' }}{{ $title }} — {{ config('app.name') }}</span>

    <div class="wrapper">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
        </div>

        @if ($typeLabel ?? null)
            @php
                $badgeClass = match($color ?? 'info') {
                    'warning' => 'badge-warning',
                    'success' => 'badge-success',
                    'danger'  => 'badge-danger',
                    default   => 'badge-info',
                };
            @endphp
            <div style="padding: 12px 32px 0;">
                <span class="type-badge {{ $badgeClass }}">{{ $typeLabel }}</span>
            </div>
        @endif

        <div class="body">
            <p><strong>{{ $title }}</strong></p>
            <p>{{ $body }}</p>
            @if (($actionUrl ?? null) && ($actionText ?? null))
                <a href="{{ $actionUrl }}" class="cta">{{ $actionText }}</a>
            @endif
        </div>

        <div class="footer">
            @if (isset($typeLabel) && str_contains(strtolower($typeLabel ?? ''), 'announcement'))
                This is a company-wide announcement from {{ config('app.name') }}. All active team members have received this email.
            @else
                You are receiving this notification based on your account preferences.
                To manage which notifications you receive, log in to {{ config('app.name') }} and visit <strong>Notification Preferences</strong> in your profile menu.
            @endif
            <br /><br />
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
