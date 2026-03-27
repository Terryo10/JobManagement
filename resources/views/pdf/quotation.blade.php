<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quotation #{{ $quotation->quotation_number }}</title>
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

        /* ── Items container with min height ── */
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

        /* ── Footer bar ── */
        .footer-bar { margin-top: 20px; background: #cc0000; color: #fff; text-align: center; padding: 8px; font-size: 11px; font-weight: 600; font-style: italic; letter-spacing: 0.05em; }

        /* ── Valid Until banner ── */
        .valid-banner { background: #fff8e1; border: 1px solid #f5a623; padding: 6px 12px; font-size: 10px; margin-bottom: 12px; }
        .valid-banner strong { color: #b8860b; }
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
                <div class="doc-type">Quotation</div>
                <div class="detail-line"><span class="detail-label">DATE :</span> {{ now()->format('d/m/Y') }}</div>
                <div class="detail-line"><span class="detail-label">PREPARED BY :</span> {{ $quotation->createdBy?->name ?? '' }}</div>
                <div class="detail-line"><span class="detail-label">QUOTATION No:</span> {{ $quotation->quotation_number }}</div>
                @if($quotation->workOrder)
                <div class="detail-line"><span class="detail-label">Reference Number:</span> {{ $quotation->workOrder->reference_number }}</div>
                @endif
                @if($quotation->valid_until)
                <div class="detail-line"><span class="detail-label">VALID UNTIL:</span> {{ $quotation->valid_until->format('d/m/Y') }}</div>
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
            <div class="customer-value">{{ $quotation->client?->company_name ?? '—' }}</div>
        </div>
        <div class="customer-row">
            <div class="customer-label">Trade Name :</div>
            <div class="customer-value">{{ $quotation->client?->contact_person ?? '' }}</div>
        </div>
        <div class="customer-row">
            <div class="customer-label">Customer Address :</div>
            <div class="customer-value">{{ $quotation->client?->address ?? '' }}{{ $quotation->client?->city ? ', ' . $quotation->client->city : '' }}</div>
        </div>
        <div class="customer-row">
            <div class="customer-label">Customer Email :</div>
            <div class="customer-value">{{ $quotation->client?->email ?? '' }}</div>
        </div>
        <div class="customer-row">
            <div class="customer-label">Customer Tel/Mobile :</div>
            <div class="customer-value">{{ $quotation->client?->phone ?? '' }}</div>
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
                @forelse($quotation->items as $item)
                @php
                    $vatPerItem = $quotation->tax_rate > 0 ? round($item->total * ($quotation->tax_rate / 100), 2) : 0;
                @endphp
                <tr>
                    <td style="width:6%">{{ (int)$item->quantity }}</td>
                    <td style="width:34%">{{ $item->description }}{{ $item->unit ? ' (' . $item->unit . ')' : '' }}</td>
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

    {{-- ── Footer Totals ── --}}
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
                <div class="total-label">SUB TOTAL EXCL {{ $quotation->currency }}</div>
                <div class="total-value">{{ number_format($quotation->subtotal, 2) }}</div>
            </div>
            <div class="total-row">
                <div class="total-label">VAT TOTAL</div>
                <div class="total-value">{{ number_format($quotation->tax_amount, 2) }}</div>
            </div>
            <div class="total-row grand">
                <div class="total-label">QUOTATION TOTAL {{ $quotation->currency }}</div>
                <div class="total-value">{{ number_format($quotation->total, 2) }}</div>
            </div>
        </div>
    </div>

    {{-- ── Notes ── --}}
    @if($quotation->notes)
    <div class="notes-section">
        <div class="notes-title">Notes:</div>
        {{ $quotation->notes }}
    </div>
    @endif

    {{-- ── Footer Bar ── --}}
    <div class="footer-bar">Bringing your brands home.</div>

</div>
</body>
</html>
