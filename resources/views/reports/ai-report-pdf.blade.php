<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AI Generated Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #222; font-size: 11px; line-height: 1.5; padding: 25px 35px; }

        /* ── Header Row ── */
        .header-row { display: table; width: 100%; margin-bottom: 25px; border-bottom: 2px solid #cc0000; padding-bottom: 15px; }
        .header-left { display: table-cell; width: 35%; vertical-align: middle; font-size: 10px; line-height: 1.6; }
        .header-center { display: table-cell; width: 30%; text-align: center; vertical-align: middle; }
        .logo-block img { max-width: 120px; height: auto; }
        .header-right { display: table-cell; width: 35%; vertical-align: middle; text-align: right; font-size: 10px; line-height: 1.6; }
        .header-left .icon, .header-right .icon { color: #cc0000; }

        /* ── Report Info ── */
        .report-title-container { margin-bottom: 20px; text-align: left; }
        .report-title { font-size: 20px; font-weight: 900; color: #cc0000; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
        .meta { font-size: 10px; color: #777; font-weight: 600; }

        /* ── Markdown Content ── */
        .content { margin-top: 15px; }
        .content h1 { font-size: 16px; color: #000; border-bottom: 1px solid #cc0000; padding-bottom: 5px; margin-top: 25px; margin-bottom: 12px; text-transform: uppercase; font-weight: 900; }
        .content h1:first-child { margin-top: 0; }
        .content h2 { font-size: 14px; color: #222; margin-top: 20px; margin-bottom: 10px; border-left: 3px solid #cc0000; padding-left: 8px; font-weight: 700; text-transform: uppercase; }
        .content h3 { font-size: 12px; color: #333; margin-top: 15px; margin-bottom: 8px; font-weight: 700; }
        .content p { margin: 8px 0; }
        .content ul, .content ol { margin: 8px 0 8px 24px; padding-left: 0; }
        .content li { margin: 4px 0; }
        
        /* ── Markdown Tables ── */
        .content table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 10px; }
        .content th { background: #f0f0f0; color: #000; padding: 7px 10px; text-align: left; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; border: 1px solid #ccc; }
        .content td { padding: 7px 10px; border: 1px solid #ccc; }
        .content tr:nth-child(even) td { background: #fafafa; }
        
        /* ── Code / Blockquotes ── */
        .content code { font-family: monospace; background: #f4f4f4; padding: 2px 5px; border-radius: 3px; font-size: 10px; border: 1px solid #ddd; }
        .content pre { background: #f4f4f4; padding: 10px; border-radius: 4px; font-size: 10px; overflow: hidden; border: 1px solid #ccc; margin: 10px 0; }
        .content blockquote { border-left: 3px solid #cc0000; padding-left: 12px; color: #555; margin: 12px 0; font-style: italic; background: #fdfdfd; padding-top: 5px; padding-bottom: 5px; }
        
        /* ── Footer ── */
        .footer-bar { margin-top: 35px; background: #cc0000; color: #fff; text-align: center; padding: 10px; font-size: 11px; font-weight: 600; font-style: italic; letter-spacing: 0.05em; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="header-row">
        <div class="header-left">
            <span class="icon">📍</span> No. 8 Donald McDonald Rd<br>
            &nbsp;&nbsp;&nbsp;&nbsp; Eastlea, Harare<br>
            <span class="icon">✉</span> sales@householdmedia.co.zw
        </div>
        <div class="header-center logo-block">
            @if(file_exists(public_path('images/logo.png')))
                <img src="{{ public_path('images/logo.png') }}" alt="Household Brands">
            @elseif(file_exists(public_path('images/logo.jpg')))
                <img src="{{ public_path('images/logo.jpg') }}" alt="Household Brands">
            @else
                <!-- Fallback until logo is uploaded to public/images/logo.png -->
                <div style="font-family: 'Arial Black', Arial, sans-serif; font-size: 26px; font-weight: 900; color: #000; padding: 20px 0;">HOUSEHOLD</div>
            @endif
        </div>
        <div class="header-right">
            <span class="icon">📞</span> +263 242 747 069-70<br>
            &nbsp;&nbsp;&nbsp;&nbsp; +263 77 410 5443<br>
            <span class="icon">🌐</span> www.householdmedia.co.zw
        </div>
    </div>


    <div class="content">
        {!! \Illuminate\Support\Str::markdown($markdown) !!}
    </div>

    <div class="footer-bar">Bringing your brands home.</div>
</body>
</html>
