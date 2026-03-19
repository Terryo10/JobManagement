<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $proposal->title ?? 'Proposal' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #222; font-size: 11px; line-height: 1.4; }

        .page { padding: 20px 30px; }

        /* ── Header ── */
        .header { border: 2px solid #cc0000; padding: 0; margin-bottom: 20px; }
        .header-top { display: table; width: 100%; }
        .header-left { display: table-cell; width: 50%; vertical-align: top; padding: 15px 20px; border-right: 2px solid #cc0000; }
        .header-right { display: table-cell; width: 50%; vertical-align: top; padding: 15px 20px; }

        .company-name { font-size: 18px; font-weight: 900; color: #000; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px; }
        .logo-block { text-align: center; }
        .logo-block svg { width: 100px; height: auto; }

        .doc-type { font-size: 20px; font-weight: 900; color: #cc0000; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 15px; }
        .detail-line { margin-bottom: 3px; font-size: 10.5px; }
        .detail-label { font-weight: 700; }

        /* ── Customer Info ── */
        .customer-section { border: 1px solid #999; margin-bottom: 20px; }
        .customer-row { display: table; width: 100%; border-bottom: 1px solid #ccc; }
        .customer-row:last-child { border-bottom: none; }
        .customer-label { display: table-cell; width: 35%; padding: 7px 10px; font-weight: 700; font-size: 10px; text-transform: uppercase; vertical-align: middle; }
        .customer-value { display: table-cell; width: 65%; padding: 7px 10px; font-size: 10.5px; vertical-align: middle; }

        /* ── Content ── */
        .proposal-title { text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 15px; text-transform: uppercase; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        .content-section { font-size: 12px; line-height: 1.6; margin-bottom: 25px; min-height: 300px; }
        
        .content-section h1 { font-size: 16px; margin: 15px 0 10px; color: #cc0000; }
        .content-section h2 { font-size: 14px; margin: 12px 0 8px; color: #333; }
        .content-section p { margin-bottom: 10px; }
        .content-section ul, .content-section ol { margin: 10px 0 10px 20px; }
        .content-section li { margin-bottom: 5px; }
        
        /* ── Value Summary ── */
        .value-section { border: 1px solid #cc0000; background: #fff; padding: 15px; text-align: center; margin-bottom: 20px; }
        .value-label { font-size: 12px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; color: #cc0000; }
        .value-amount { font-size: 20px; font-weight: 900; }

        /* ── Notes ── */
        .notes-section { margin-top: 15px; padding: 10px 15px; border: 1px dashed #ccc; font-size: 10px; background: #f9f9f9; }
        .notes-title { font-weight: 700; text-transform: uppercase; margin-bottom: 5px; }

        /* ── Footer bar ── */
        .footer-bar { margin-top: 30px; background: #cc0000; color: #fff; text-align: center; padding: 8px; font-size: 11px; font-weight: 600; font-style: italic; letter-spacing: 0.05em; }
    </style>
</head>
<body>
<div class="page">

    {{-- ── Header ── --}}
    <div class="header">
        <div class="header-top">
            <div class="header-left">
                <div class="company-name">Household Brands (Pvt) Ltd</div>
                <div class="logo-block">
                    @if(file_exists(public_path('images/logo.png')))
                        <img src="{{ public_path('images/logo.png') }}" alt="Household Brands" style="max-width: 150px; height: auto;">
                    @elseif(file_exists(public_path('images/logo.jpg')))
                        <img src="{{ public_path('images/logo.jpg') }}" alt="Household Brands" style="max-width: 150px; height: auto;">
                    @else
                        <div style="font-family: 'Arial Black', Arial, sans-serif; font-size: 26px; font-weight: 900; color: #000; padding: 20px 0;">HOUSEHOLD</div>
                    @endif
                </div>
            </div>
            <div class="header-right">
                <div class="doc-type">{{ ucfirst($proposal->type ?? 'Proposal') }}</div>
                <div class="detail-line"><span class="detail-label">DATE SUBMITTED :</span> {{ $proposal->submitted_at ? $proposal->submitted_at->format('d/m/Y') : now()->format('d/m/Y') }}</div>
                <div class="detail-line"><span class="detail-label">VALID UNTIL :</span> {{ $proposal->valid_until ? $proposal->valid_until->format('d/m/Y') : 'N/A' }}</div>
                <div class="detail-line"><span class="detail-label">PREPARED BY :</span> {{ $proposal->preparedBy?->name ?? 'Household Brands Team' }}</div>
                <br>
                <div class="detail-line"><span class="detail-label">ADDRESS:</span> Office 6, 146 Samora Machel Ave, Harare</div>
                <div class="detail-line"><span class="detail-label">EMAIL:</span> lovett@householdmedia.co.zw</div>
                <div class="detail-line"><span class="detail-label">PHONE:</span> 0774105443</div>
            </div>
        </div>
    </div>

    {{-- ── Client Section ── --}}
    <div class="customer-section">
        <div class="customer-row">
            <div class="customer-label">Prepared For :</div>
            <div class="customer-value">
                @if($proposal->client)
                    <strong>{{ $proposal->client->company_name }}</strong><br>
                    {{ $proposal->client->contact_person }}<br>
                    {{ $proposal->client->email }} | {{ $proposal->client->phone }}
                @elseif($proposal->lead)
                    <strong>{{ $proposal->lead->contact_name }}</strong><br>
                    {{ $proposal->lead->company_name }}<br>
                    {{ $proposal->lead->email }} | {{ $proposal->lead->phone }}
                @else
                    —
                @endif
            </div>
        </div>
        <div class="customer-row">
            <div class="customer-label">Proposal Title :</div>
            <div class="customer-value" style="font-weight: bold; font-size: 12px;">{{ $proposal->title }}</div>
        </div>
    </div>

    {{-- ── Content ── --}}
    <div class="content-section">
        {!! $proposal->content !!}
    </div>

    {{-- ── Value Summary ── --}}
    @if($proposal->value > 0)
    <div class="value-section">
        <div class="value-label">Total Proposed Investment</div>
        <div class="value-amount">{{ $proposal->currency }} {{ number_format($proposal->value, 2) }}</div>
    </div>
    @endif

    {{-- ── Notes ── --}}
    @if($proposal->notes)
    <div class="notes-section">
        <div class="notes-title">Additional Notes:</div>
        {!! nl2br(e($proposal->notes)) !!}
    </div>
    @endif

    {{-- ── Footer Bar ── --}}
    <div class="footer-bar">Bringing your brands home.</div>

</div>
</body>
</html>
