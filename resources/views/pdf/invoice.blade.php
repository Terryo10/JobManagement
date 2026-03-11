<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
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

        .doc-type { font-size: 22px; font-weight: 900; color: #cc0000; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 15px; }
        .detail-line { margin-bottom: 3px; font-size: 10.5px; }
        .detail-label { font-weight: 700; }

        /* ── Customer Info ── */
        .customer-section { border: 1px solid #999; margin-bottom: 15px; }
        .customer-row { display: table; width: 100%; border-bottom: 1px solid #ccc; }
        .customer-row:last-child { border-bottom: none; }
        .customer-label { display: table-cell; width: 35%; padding: 5px 10px; font-weight: 700; font-size: 10px; text-transform: uppercase; vertical-align: middle; }
        .customer-value { display: table-cell; width: 65%; padding: 5px 10px; font-size: 10.5px; vertical-align: middle; }

        /* ── Items Table ── */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        .items-table th { background: #222; color: #fff; padding: 7px 10px; font-size: 9px; text-transform: uppercase; letter-spacing: 0.08em; text-align: left; font-weight: 700; }
        .items-table td { padding: 7px 10px; border-bottom: 1px solid #ddd; font-size: 10.5px; }
        .items-table .text-right { text-align: right; }
        .items-table tbody tr:nth-child(even) { background: #f9f9f9; }

        /* ── Items container with min height for blank space ── */
        .items-wrapper { border: 1px solid #999; border-top: none; min-height: 280px; position: relative; }

        /* ── Footer / Totals ── */
        .footer-section { display: table; width: 100%; border: 1px solid #999; border-top: none; }
        .bank-details { display: table-cell; width: 50%; padding: 10px 15px; vertical-align: top; border-right: 1px solid #999; }
        .bank-details-title { font-weight: 700; font-size: 10px; text-transform: uppercase; margin-bottom: 5px; }
        .bank-line { font-size: 10px; margin-bottom: 2px; }

        .totals-cell { display: table-cell; width: 50%; vertical-align: top; }
        .total-row { display: table; width: 100%; border-bottom: 1px solid #ccc; }
        .total-row:last-child { border-bottom: none; }
        .total-label { display: table-cell; width: 60%; padding: 7px 12px; font-weight: 700; font-size: 10px; text-align: center; text-transform: uppercase; }
        .total-value { display: table-cell; width: 40%; padding: 7px 12px; font-weight: 600; font-size: 11px; text-align: right; }
        .total-row.grand { background: #f0f0f0; }
        .total-row.grand .total-label, .total-row.grand .total-value { font-size: 12px; font-weight: 900; }

        /* ── Notes ── */
        .notes-section { margin-top: 12px; padding: 8px 12px; border: 1px solid #ccc; font-size: 10px; }
        .notes-title { font-weight: 700; text-transform: uppercase; margin-bottom: 3px; }

        /* ── Signature ── */
        .signature-section { margin-top: 30px; border-top: 1px dashed #ccc; padding-top: 15px; width: 50%; }
        .signature-img { max-height: 60px; display: block; margin: 8px 0; }

        /* ── Footer bar ── */
        .footer-bar { margin-top: 20px; background: #cc0000; color: #fff; text-align: center; padding: 8px; font-size: 11px; font-weight: 600; font-style: italic; letter-spacing: 0.05em; }
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
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 160" width="120">
                        <rect x="10" y="5" width="16" height="55" fill="#cc0000"/>
                        <rect x="36" y="5" width="16" height="35" fill="#cc0000"/>
                        <rect x="36" y="28" width="40" height="12" fill="#cc0000"/>
                        <rect x="62" y="5" width="16" height="55" fill="#cc0000"/>
                        <rect x="88" y="5" width="16" height="55" fill="#cc0000"/>
                        <text x="5" y="85" font-family="Arial Black, Arial" font-size="28" font-weight="900" fill="#000">HOUSEHOLD</text>
                        <rect x="10" y="100" width="16" height="55" fill="#cc0000"/>
                        <rect x="36" y="100" width="16" height="55" fill="#cc0000"/>
                        <rect x="36" y="115" width="40" height="12" fill="#cc0000"/>
                        <rect x="62" y="120" width="16" height="35" fill="#cc0000"/>
                        <rect x="88" y="100" width="16" height="55" fill="#cc0000"/>
                    </svg>
                </div>
            </div>
            <div class="header-right">
                <div class="doc-type">Invoice</div>
                <div class="detail-line"><span class="detail-label">DATE :</span> {{ $invoice->issued_at ? $invoice->issued_at->format('d/m/Y') : now()->format('d/m/Y') }}</div>
                <div class="detail-line"><span class="detail-label">SALES PERSON :</span> {{ $invoice->createdBy?->name ?? '' }}</div>
                <div class="detail-line"><span class="detail-label">Invoice No:</span> {{ $invoice->invoice_number }}</div>
                @if($invoice->workOrder)
                <div class="detail-line"><span class="detail-label">Reference Number:</span> {{ $invoice->workOrder->reference_number }}</div>
                @endif
                <div class="detail-line"><span class="detail-label">OUR VAT NO. :</span> 220284500</div>
                <div class="detail-line"><span class="detail-label">OUR TIN NO. :</span> 2000885268</div>
                <div class="detail-line"><span class="detail-label">ADDRESS:</span> Office 6, 146 Samora Machel Ave, Harare</div>
                <div class="detail-line"><span class="detail-label">EMAIL ADRESS:</span> lovett@householdmedia.co.zw</div>
                <div class="detail-line"><span class="detail-label">CONTACT NUMBER:</span> 0774105443</div>
            </div>
        </div>
    </div>

    {{-- ── Customer Section ── --}}
    <div class="customer-section">
        <div class="customer-row">
            <div class="customer-label">Customer Registered Name :</div>
            <div class="customer-value">{{ $invoice->client?->company_name ?? '—' }}</div>
        </div>
        <div class="customer-row">
            <div class="customer-label">Trade Name :</div>
            <div class="customer-value">{{ $invoice->client?->contact_person ?? '' }}</div>
        </div>
        <div class="customer-row">
            <div class="customer-label">Customer Address :</div>
            <div class="customer-value">{{ $invoice->client?->address ?? '' }}{{ $invoice->client?->city ? ', ' . $invoice->client->city : '' }}</div>
        </div>
        <div class="customer-row">
            <div class="customer-label">Customer Email :</div>
            <div class="customer-value">{{ $invoice->client?->email ?? '' }}</div>
        </div>
        <div class="customer-row">
            <div class="customer-label">Customer Tel/Mobile :</div>
            <div class="customer-value">{{ $invoice->client?->phone ?? '' }}</div>
        </div>
    </div>

    {{-- ── Items Table ── --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:6%">QTY</th>
                <th style="width:34%">DESCRIPTION</th>
                <th style="width:15%" class="text-right">UNIT PRICE</th>
                <th style="width:15%" class="text-right">VAT AMT</th>
                <th style="width:15%" class="text-right">TOTAL (EXCL)</th>
                <th style="width:15%" class="text-right">TOTAL (INCL)</th>
            </tr>
        </thead>
    </table>
    <div class="items-wrapper">
        <table class="items-table" style="border: none;">
            <tbody>
                @forelse($invoice->items as $item)
                @php
                    $vatPerItem = $invoice->tax_rate > 0 ? round($item->total * ($invoice->tax_rate / 100), 2) : 0;
                @endphp
                <tr>
                    <td style="width:6%">{{ (int)$item->quantity }}</td>
                    <td style="width:34%">{{ $item->description }}</td>
                    <td style="width:15%" class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td style="width:15%" class="text-right">{{ number_format($vatPerItem, 2) }}</td>
                    <td style="width:15%" class="text-right">{{ number_format($item->total, 2) }}</td>
                    <td style="width:15%" class="text-right">{{ number_format($item->total + $vatPerItem, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #999;">No line items.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Footer Totals + Bank Details ── --}}
    <div class="footer-section">
        <div class="bank-details">
            <div class="bank-details-title">Bank Details</div>
            <div class="bank-line"><strong>Acc Name:</strong> Household Brands (Pvt) Ltd</div>
            <div class="bank-line"><strong>Bank:</strong> NMB Bank</div>
            <div class="bank-line"><strong>Branch:</strong> Eastgate</div>
            <div class="bank-line"><strong>Acc No:</strong> 100040041620</div>
        </div>
        <div class="totals-cell">
            <div class="total-row">
                <div class="total-label">SUB TOTAL EXCL {{ $invoice->currency }}</div>
                <div class="total-value">{{ number_format($invoice->subtotal, 2) }}</div>
            </div>
            <div class="total-row">
                <div class="total-label">VAT TOTAL</div>
                <div class="total-value">{{ number_format($invoice->tax_amount, 2) }}</div>
            </div>
            <div class="total-row grand">
                <div class="total-label">INVOICE TOTAL {{ $invoice->currency }}</div>
                <div class="total-value">{{ number_format($invoice->total, 2) }}</div>
            </div>
        </div>
    </div>

    {{-- ── Notes ── --}}
    @if($invoice->notes)
    <div class="notes-section">
        <div class="notes-title">Notes:</div>
        {{ $invoice->notes }}
    </div>
    @endif

    {{-- ── Signature ── --}}
    @if($invoice->client_signature)
    <div class="signature-section">
        <strong>Authorized Signature:</strong>
        <img src="{{ $invoice->client_signature }}" class="signature-img" alt="Client Signature">
        <p style="font-size: 9px; color: #777;">
            Signed on: {{ $invoice->client_signature_date ? $invoice->client_signature_date->format('d M Y h:i A') : 'N/A' }}<br>
            IP Address: {{ $invoice->client_ip }}
        </p>
    </div>
    @endif

    {{-- ── Footer Bar ── --}}
    <div class="footer-bar">Bringing your brands home.</div>

</div>
</body>
</html>
