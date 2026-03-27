<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Requisition — {{ $purchaseOrder->reference_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #222; font-size: 12px; line-height: 1.5; }

        .page { padding: 30px 40px; }

        /* ── Title ── */
        .doc-title { text-align: center; font-size: 22px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 20px; }

        /* ── Header Row ── */
        .header-row { display: table; width: 100%; margin-bottom: 25px; border-bottom: 2px solid #cc0000; padding-bottom: 15px; }
        .header-left { display: table-cell; width: 35%; vertical-align: middle; font-size: 10px; line-height: 1.6; }
        .header-center { display: table-cell; width: 30%; text-align: center; vertical-align: middle; }
        .header-center svg { width: 90px; height: auto; }
        .header-right { display: table-cell; width: 35%; vertical-align: middle; text-align: right; font-size: 10px; line-height: 1.6; }
        .header-left .icon, .header-right .icon { color: #cc0000; }

        /* ── Form Fields ── */
        .form-row { display: table; width: 100%; margin-bottom: 18px; }
        .form-label { display: table-cell; width: 30%; font-weight: 700; font-size: 11px; text-transform: uppercase; vertical-align: bottom; padding-bottom: 3px; }
        .form-value { display: table-cell; width: 70%; border-bottom: 1px dotted #999; padding-bottom: 3px; font-size: 12px; padding-left: 8px; }

        /* ── Section Label ── */
        .section-label { font-weight: 700; font-size: 11px; text-transform: uppercase; margin-bottom: 8px; margin-top: 15px; }

        /* ── Details lines ── */
        .detail-lines { margin-bottom: 15px; }
        .detail-line { border-bottom: 1px dotted #999; height: 28px; margin-bottom: 5px; }

        /* ── Signature rows ── */
        .signature-block { margin-top: 30px; }
        .signature-row { display: table; width: 100%; margin-bottom: 22px; }
        .signature-role { display: table-cell; width: 45%; vertical-align: bottom; }
        .signature-role-label { font-weight: 700; font-size: 11px; text-transform: uppercase; display: inline; }
        .signature-dots { border-bottom: 1px dotted #999; display: inline-block; width: 220px; }
        .signature-sign { display: table-cell; width: 55%; vertical-align: bottom; text-align: left; padding-left: 20px; }
        .signature-sign-label { font-weight: 700; font-size: 11px; text-transform: uppercase; display: inline; }
        .signature-sign-dots { border-bottom: 1px dotted #999; display: inline-block; width: 260px; }
        .sig-img { height: 48px; max-width: 240px; display: block; margin-bottom: 2px; }
        .sig-date { font-size: 9px; color: #666; }

        /* ── Items Table ── */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; margin-top: 10px; }
        .items-table th { background: #f0f0f0; padding: 6px 8px; font-size: 9px; text-transform: uppercase; letter-spacing: 0.05em; text-align: left; border: 1px solid #ccc; }
        .items-table td { padding: 6px 8px; border: 1px solid #ccc; font-size: 10.5px; }
        .items-table .text-right { text-align: right; }

        /* ── Totals ── */
        .totals { width: 50%; margin-left: auto; margin-bottom: 15px; }
        .totals .total-row { display: table; width: 100%; border-bottom: 1px solid #ccc; }
        .totals .total-label { display: table-cell; width: 50%; padding: 5px 8px; font-weight: 700; font-size: 10px; }
        .totals .total-value { display: table-cell; width: 50%; padding: 5px 8px; text-align: right; font-size: 11px; }
        .totals .total-row.grand { background: #f0f0f0; }
        .totals .total-row.grand .total-label, .totals .total-row.grand .total-value { font-weight: 900; }

        /* ── Footer ── */
        .footer-bar { margin-top: 30px; background: #cc0000; color: #fff; text-align: center; padding: 10px; font-size: 11px; font-weight: 600; font-style: italic; letter-spacing: 0.05em; border-radius: 4px; }
    </style>
</head>
<body>
<div class="page">

    {{-- ── Title ── --}}
    <div class="doc-title">Payment Requisition</div>

    {{-- ── Header ── --}}
    <div class="header-row">
        <div class="header-left">
            <span class="icon">📍</span> No. 8 Donald McDonald Rd<br>
            &nbsp;&nbsp;&nbsp;&nbsp; Eastlea, Harare<br>
            <span class="icon">✉</span> sales@householdmedia.co.zw
        </div>
        <div class="header-center logo-block">
            @if(file_exists(public_path('images/logo.png')))
                <img src="{{ public_path('images/logo.png') }}" alt="Household Brands" style="max-width: 120px; height: auto;">
            @elseif(file_exists(public_path('images/logo.jpg')))
                <img src="{{ public_path('images/logo.jpg') }}" alt="Household Brands" style="max-width: 120px; height: auto;">
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

    {{-- ── Form Fields ── --}}
    <div class="form-row">
        <div class="form-label">Purpose</div>
        <div class="form-value">{{ $purchaseOrder->title ?? '—' }}</div>
    </div>
    <div class="form-row">
        <div class="form-label">Amount</div>
        <div class="form-value">${{ number_format($purchaseOrder->total_amount, 2) }}</div>
    </div>
    <div class="form-row">
        <div class="form-label">Date</div>
        <div class="form-value">{{ $purchaseOrder->created_at?->format('d / m / Y') ?? now()->format('d / m / Y') }}</div>
    </div>
    <div class="form-row">
        <div class="form-label">Payment Type</div>
        <div class="form-value">PETTY CASH USD / BANK TRANSFER</div>
    </div>
    <div class="form-row">
        <div class="form-label">Reference No.</div>
        <div class="form-value">{{ $purchaseOrder->reference_number }}</div>
    </div>
    @if($purchaseOrder->gl_account)
    <div class="form-row">
        <div class="form-label">GL Account</div>
        <div class="form-value">{{ $purchaseOrder->gl_account }}{{ $purchaseOrder->gl_account_name ? ' — ' . $purchaseOrder->gl_account_name : '' }}</div>
    </div>
    @endif

    {{-- ── Details of Payment ── --}}
    <div class="section-label">Details of Payment:</div>
    <div class="form-row">
        <div class="form-label">Description</div>
        <div class="form-value">{{ $purchaseOrder->title ?? '—' }}</div>
    </div>
    @if($purchaseOrder->notes)
    <div class="form-row">
        <div class="form-label">Notes</div>
        <div class="form-value">{{ $purchaseOrder->notes }}</div>
    </div>
    @endif
    <div class="totals">
        <div class="total-row grand">
            <div class="total-label">AMOUNT REQUESTED</div>
            <div class="total-value">${{ number_format($purchaseOrder->total_amount, 2) }}</div>
        </div>
    </div>




    {{-- ── Signatures ── --}}
    <div class="signature-block">
        {{-- Requested By --}}
        <div class="signature-row">
            <div class="signature-role">
                <span class="signature-role-label">REQUESTED BY</span>
                <span class="signature-dots">&nbsp;{{ $purchaseOrder->orderedBy?->name ?? '' }}&nbsp;</span>
            </div>
            <div class="signature-sign">
                <span class="signature-sign-label">SIGNATURE</span>
                <span class="signature-sign-dots">&nbsp;</span>
            </div>
        </div>

        {{-- Finance Reviewed By --}}
        <div class="signature-row">
            <div class="signature-role">
                <span class="signature-role-label">REVIEWED BY (FINANCE)</span>
                <span class="signature-dots">&nbsp;{{ $purchaseOrder->financeApprovedBy?->name ?? '' }}&nbsp;</span>
                @if($purchaseOrder->finance_signature_date)
                    <div class="sig-date">{{ $purchaseOrder->finance_signature_date->format('d M Y H:i') }}</div>
                @endif
            </div>
            <div class="signature-sign">
                <span class="signature-sign-label">SIGNATURE</span>
                @if($purchaseOrder->finance_signature)
                    <img src="{{ $purchaseOrder->finance_signature }}" class="sig-img" alt="Finance Signature">
                @else
                    <span class="signature-sign-dots">&nbsp;</span>
                @endif
            </div>
        </div>

        {{-- Authorised By (Admin) --}}
        <div class="signature-row">
            <div class="signature-role">
                <span class="signature-role-label">AUTHORISED BY</span>
                <span class="signature-dots">&nbsp;{{ $purchaseOrder->approvedBy?->name ?? '' }}&nbsp;</span>
                @if($purchaseOrder->admin_signature_date)
                    <div class="sig-date">{{ $purchaseOrder->admin_signature_date->format('d M Y H:i') }}</div>
                @endif
            </div>
            <div class="signature-sign">
                <span class="signature-sign-label">SIGNATURE</span>
                @if($purchaseOrder->admin_signature)
                    <img src="{{ $purchaseOrder->admin_signature }}" class="sig-img" alt="Admin Signature">
                @else
                    <span class="signature-sign-dots">&nbsp;</span>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Footer ── --}}
    <div class="footer-bar">Bringing your brands home.</div>

</div>
</body>
</html>
