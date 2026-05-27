<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $submission->title ?? 'PRAZ Submission' }}</title>
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

        .doc-type { font-size: 18px; font-weight: 900; color: #cc0000; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 15px; }
        .detail-line { margin-bottom: 3px; font-size: 10.5px; }
        .detail-label { font-weight: 700; }

        /* ── Info Section ── */
        .info-section { border: 1px solid #999; margin-bottom: 20px; }
        .info-row { display: table; width: 100%; border-bottom: 1px solid #ccc; }
        .info-row:last-child { border-bottom: none; }
        .info-label { display: table-cell; width: 35%; padding: 7px 10px; font-weight: 700; font-size: 10px; text-transform: uppercase; vertical-align: middle; background: #f5f5f5; }
        .info-value { display: table-cell; width: 65%; padding: 7px 10px; font-size: 10.5px; vertical-align: middle; }

        /* ── Content ── */
        .section-title { font-size: 14px; font-weight: bold; text-transform: uppercase; border-bottom: 2px solid #cc0000; padding-bottom: 5px; margin: 20px 0 10px; color: #cc0000; }
        .content-section { font-size: 12px; line-height: 1.6; margin-bottom: 20px; min-height: 100px; }
        .content-section h1 { font-size: 16px; margin: 15px 0 10px; color: #cc0000; }
        .content-section h2 { font-size: 14px; margin: 12px 0 8px; color: #333; }
        .content-section p { margin-bottom: 10px; }
        .content-section ul, .content-section ol { margin: 10px 0 10px 20px; }
        .content-section li { margin-bottom: 5px; }

        /* ── Value Summary ── */
        .value-section { border: 1px solid #cc0000; background: #fff; padding: 15px; text-align: center; margin-bottom: 20px; }
        .value-label { font-size: 12px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; color: #cc0000; }
        .value-amount { font-size: 20px; font-weight: 900; }

        /* ── Status Badge ── */
        .status-badge { display: inline-block; padding: 4px 12px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; border-radius: 3px; }
        .status-draft { background: #e5e7eb; color: #374151; }
        .status-submitted { background: #dbeafe; color: #1e40af; }
        .status-under_review { background: #fef3c7; color: #92400e; }
        .status-approved { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .status-expired { background: #f3f4f6; color: #6b7280; }

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
                <div class="doc-type">PRAZ Submission</div>
                <div class="detail-line"><span class="detail-label">REFERENCE :</span> {{ $submission->reference_number }}</div>
                @if($submission->tender_number)
                    <div class="detail-line"><span class="detail-label">TENDER NO. :</span> {{ $submission->tender_number }}</div>
                @endif
                <div class="detail-line"><span class="detail-label">DEADLINE :</span> {{ $submission->submission_deadline->format('d/m/Y H:i') }}</div>
                <div class="detail-line"><span class="detail-label">SUBMITTED :</span> {{ $submission->submitted_at ? $submission->submitted_at->format('d/m/Y') : 'Not yet submitted' }}</div>
                <div class="detail-line"><span class="detail-label">PREPARED BY :</span> {{ $submission->preparedBy?->name ?? 'Household Brands Team' }}</div>
                <br>
                <div class="detail-line"><span class="detail-label">ADDRESS:</span> Office 6, 146 Samora Machel Ave, Harare</div>
                <div class="detail-line"><span class="detail-label">EMAIL:</span> lovett@householdmedia.co.zw</div>
                <div class="detail-line"><span class="detail-label">PHONE:</span> 0774105443</div>
            </div>
        </div>
    </div>

    {{-- ── Submission Info ── --}}
    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Submission Title :</div>
            <div class="info-value" style="font-weight: bold; font-size: 12px;">{{ $submission->title }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Category :</div>
            <div class="info-value">{{ ucfirst($submission->category) }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Procuring Entity :</div>
            <div class="info-value"><strong>{{ $submission->procuring_entity }}</strong></div>
        </div>
        @if($submission->client)
        <div class="info-row">
            <div class="info-label">Client :</div>
            <div class="info-value">
                <strong>{{ $submission->client->company_name }}</strong><br>
                {{ $submission->client->contact_person }}<br>
                {{ $submission->client->email }} | {{ $submission->client->phone }}
            </div>
        </div>
        @endif
        <div class="info-row">
            <div class="info-label">Status :</div>
            <div class="info-value">
                <span class="status-badge status-{{ $submission->status }}">{{ str_replace('_', ' ', $submission->status) }}</span>
            </div>
        </div>
    </div>

    {{-- ── Bid Value ── --}}
    @if($submission->value > 0)
    <div class="value-section">
        <div class="value-label">Bid Value</div>
        <div class="value-amount">{{ $submission->currency }} {{ number_format($submission->value, 2) }}</div>
    </div>
    @endif

    {{-- ── Description ── --}}
    @if($submission->description)
    <div class="section-title">Description</div>
    <div class="content-section">
        {!! $submission->description !!}
    </div>
    @endif

    {{-- ── Outcome Notes ── --}}
    @if($submission->outcome_notes)
    <div class="notes-section">
        <div class="notes-title">Outcome / Feedback:</div>
        {!! nl2br(e($submission->outcome_notes)) !!}
    </div>
    @endif

    {{-- ── Internal Notes ── --}}
    @if($submission->notes)
    <div class="notes-section" style="margin-top: 10px;">
        <div class="notes-title">Internal Notes:</div>
        {!! nl2br(e($submission->notes)) !!}
    </div>
    @endif

    {{-- ── Footer Bar ── --}}
    <div class="footer-bar">Bringing your brands home.</div>

</div>
</body>
</html>
