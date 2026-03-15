<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>AI Generated Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a202c; margin: 30px; line-height: 1.6; }
        h1 { font-size: 22px; color: #1e3a5f; border-bottom: 2px solid #1e3a5f; padding-bottom: 8px; margin-bottom: 4px; }
        h2 { font-size: 16px; color: #2d3748; margin-top: 20px; margin-bottom: 8px; border-left: 4px solid #f59e0b; padding-left: 8px; }
        h3 { font-size: 13px; color: #4a5568; margin-top: 14px; margin-bottom: 6px; }
        p { margin: 6px 0; }
        ul, ol { margin: 6px 0 6px 18px; }
        li { margin: 3px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 10px; }
        th { background: #1e3a5f; color: #fff; padding: 6px 8px; text-align: left; }
        td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; }
        tr:nth-child(even) td { background: #f7fafc; }
        code { font-family: monospace; background: #edf2f7; padding: 1px 4px; border-radius: 3px; font-size: 10px; }
        pre { background: #edf2f7; padding: 10px; border-radius: 4px; font-size: 10px; overflow: hidden; }
        strong { font-weight: bold; }
        em { font-style: italic; }
        .meta { font-size: 10px; color: #718096; margin-bottom: 20px; }
        .footer { margin-top: 30px; font-size: 9px; color: #a0aec0; text-align: right; border-top: 1px solid #e2e8f0; padding-top: 6px; }
        blockquote { border-left: 3px solid #e2e8f0; padding-left: 10px; color: #718096; margin: 8px 0; }
    </style>
</head>
<body>
    <h1>AI-Generated Report — Household Media</h1>
    <div class="meta">Generated: {{ $generatedAt }}</div>

    {!! \Illuminate\Support\Str::markdown($markdown) !!}

    <div class="footer">Household Media — Job Management System &nbsp;|&nbsp; AI-Generated Report</div>
</body>
</html>
