<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job Card {{ $workOrder->reference_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #222; font-size: 10.5px; line-height: 1.4; }

        .page { padding: 20px 28px; }

        /* ── Header ── */
        .header { border: 2px solid #cc0000; margin-bottom: 16px; }
        .header-top { display: table; width: 100%; }
        .header-left { display: table-cell; width: 55%; vertical-align: top; padding: 14px 18px; border-right: 2px solid #cc0000; }
        .header-right { display: table-cell; width: 45%; vertical-align: top; padding: 14px 18px; }

        .company-name { font-size: 17px; font-weight: 900; color: #000; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; }
        .company-sub { font-size: 9px; color: #555; margin-bottom: 10px; }

        .doc-type { font-size: 20px; font-weight: 900; color: #cc0000; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 12px; }
        .detail-line { margin-bottom: 3px; font-size: 10px; }
        .detail-label { font-weight: 700; }

        /* Status badge */
        .badge { display: inline-block; padding: 1px 8px; border-radius: 3px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
        .badge-gray    { background: #e5e7eb; color: #374151; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info    { background: #dbeafe; color: #1e40af; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger  { background: #fee2e2; color: #991b1b; }

        /* ── Section ── */
        .section { margin-bottom: 14px; border: 1px solid #ccc; }
        .section-heading { background: #2d2d2d; color: #fff; font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; padding: 5px 10px; }
        .section-body { padding: 0; }

        /* ── Data grid ── */
        .data-row { display: table; width: 100%; border-bottom: 1px solid #e5e5e5; }
        .data-row:last-child { border-bottom: none; }
        .data-label { display: table-cell; width: 26%; padding: 5px 10px; font-weight: 700; font-size: 9.5px; text-transform: uppercase; color: #555; vertical-align: top; background: #f9f9f9; border-right: 1px solid #e5e5e5; }
        .data-value { display: table-cell; padding: 5px 10px; vertical-align: top; font-size: 10px; }

        /* ── Two-column within a row ── */
        .two-col { display: table; width: 100%; }
        .col-half { display: table-cell; width: 50%; }
        .col-half .data-row { border-right: 1px solid #e5e5e5; }
        .col-half:last-child .data-row { border-right: none; }

        /* ── Signatures ── */
        .sig-table { display: table; width: 100%; }
        .sig-cell { display: table-cell; width: 33.33%; padding: 10px 12px; border-right: 1px solid #ddd; vertical-align: top; }
        .sig-cell:last-child { border-right: none; }
        .sig-label { font-size: 8.5px; font-weight: 700; text-transform: uppercase; color: #777; margin-bottom: 4px; }
        .sig-value { font-size: 10px; min-height: 18px; border-bottom: 1px solid #bbb; padding-bottom: 2px; }
        .sig-placeholder { color: #bbb; font-style: italic; }

        /* ── Footer ── */
        .footer { margin-top: 16px; border-top: 1px solid #ccc; padding-top: 6px; display: table; width: 100%; }
        .footer-left  { display: table-cell; width: 60%; font-size: 8.5px; color: #777; }
        .footer-right { display: table-cell; width: 40%; text-align: right; font-size: 8.5px; color: #777; }
    </style>
</head>
<body>
<div class="page">

    {{-- ─── HEADER ─────────────────────────────────────────────────────────── --}}
    <div class="header">
        <div class="header-top">
            <div class="header-left">
                <div class="logo-block" style="margin-bottom: 10px;">
                    @if(file_exists(public_path('images/logo.png')))
                        <img src="{{ public_path('images/logo.png') }}" alt="Household Brands" style="max-width: 120px; height: auto;">
                    @elseif(file_exists(public_path('images/logo.jpg')))
                        <img src="{{ public_path('images/logo.jpg') }}" alt="Household Brands" style="max-width: 120px; height: auto;">
                    @else
                        <!-- Fallback until logo is uploaded to public/images/logo.png -->
                        <div style="font-family: 'Arial Black', Arial, sans-serif; font-size: 26px; font-weight: 900; color: #000; padding: 20px 0;">HOUSEHOLD</div>
                    @endif
                </div>
                <div class="company-name">{{ config('app.name', 'Job Management') }}</div>
                <div class="company-sub">Job Card Document</div>
            </div>
            <div class="header-right">
                <div class="doc-type">Job Card</div>
                <div class="detail-line"><span class="detail-label">Reference:</span> {{ $workOrder->reference_number }}</div>
                <div class="detail-line"><span class="detail-label">Status:</span>
                    @php
                        $statusColors = ['pending'=>'badge-gray','in_progress'=>'badge-warning','on_hold'=>'badge-info','completed'=>'badge-success','cancelled'=>'badge-danger'];
                        $statusColor = $statusColors[$workOrder->status] ?? 'badge-gray';
                        $statusLabel = ucwords(str_replace('_', ' ', $workOrder->status));
                    @endphp
                    <span class="badge {{ $statusColor }}">{{ $statusLabel }}</span>
                </div>
                <div class="detail-line"><span class="detail-label">Priority:</span>
                    @php
                        $priorityColors = ['low'=>'badge-gray','normal'=>'badge-info','high'=>'badge-warning','urgent'=>'badge-danger'];
                        $priorityColor = $priorityColors[$workOrder->priority] ?? 'badge-gray';
                    @endphp
                    <span class="badge {{ $priorityColor }}">{{ ucfirst($workOrder->priority ?? '—') }}</span>
                </div>
                <div class="detail-line"><span class="detail-label">Category:</span> {{ ucwords(str_replace('_', ' ', $workOrder->category ?? '—')) }}</div>
                <div class="detail-line"><span class="detail-label">Generated:</span> {{ $generatedAt }}</div>
            </div>
        </div>
    </div>

    {{-- ─── GENERAL INFORMATION ────────────────────────────────────────────── --}}
    <div class="section">
        <div class="section-heading">General Information</div>
        <div class="section-body">
            <div class="data-row">
                <div class="data-label">Job Title</div>
                <div class="data-value">{{ $workOrder->title ?? '—' }}</div>
            </div>
            <div class="two-col">
                <div class="col-half">
                    <div class="data-row">
                        <div class="data-label">Client</div>
                        <div class="data-value">{{ $workOrder->client?->company_name ?? '—' }}</div>
                    </div>
                </div>
                <div class="col-half">
                    <div class="data-row">
                        <div class="data-label">Department</div>
                        <div class="data-value">{{ $workOrder->assignedDepartment?->name ?? '—' }}</div>
                    </div>
                </div>
            </div>
            <div class="two-col">
                <div class="col-half">
                    <div class="data-row">
                        <div class="data-label">Lead Person</div>
                        <div class="data-value">{{ $workOrder->details['lead_person'] ?? '—' }}</div>
                    </div>
                </div>
                <div class="col-half">
                    <div class="data-row">
                        <div class="data-label">Claimed By</div>
                        <div class="data-value">{{ $workOrder->claimedBy?->name ?? 'Unclaimed' }}</div>
                    </div>
                </div>
            </div>
            <div class="two-col">
                <div class="col-half">
                    <div class="data-row">
                        <div class="data-label">Start Date</div>
                        <div class="data-value">{{ $workOrder->start_date?->format('d M Y') ?? '—' }}</div>
                    </div>
                </div>
                <div class="col-half">
                    <div class="data-row">
                        <div class="data-label">Deadline</div>
                        <div class="data-value">{{ $workOrder->deadline?->format('d M Y') ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── DESIGN JOB CARD ────────────────────────────────────────────────── --}}
    <div class="section">
        <div class="section-heading">Design Job Card</div>
        <div class="section-body">
            <div class="two-col">
                <div class="col-half">
                    <div class="data-row">
                        <div class="data-label">Date Order Received</div>
                        <div class="data-value">{{ !empty($workOrder->details['date_order_received']) ? \Carbon\Carbon::parse($workOrder->details['date_order_received'])->format('d M Y') : '—' }}</div>
                    </div>
                </div>
                <div class="col-half">
                    <div class="data-row">
                        <div class="data-label">Deadline</div>
                        <div class="data-value">{{ $workOrder->deadline?->format('d M Y') ?? '—' }}</div>
                    </div>
                </div>
            </div>
            @if(!empty($workOrder->description))
            <div class="data-row">
                <div class="data-label">Project Description</div>
                <div class="data-value">{{ strip_tags($workOrder->description) }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- ─── PROCUREMENT ─────────────────────────────────────────────────────── --}}
    @php $d = $workOrder->details ?? []; @endphp
    @if(!empty($d['logistics']) || !empty($d['procurement_details']) || !empty($d['supplier_name']) || !empty($workOrder->budget))
    <div class="section">
        <div class="section-heading">Procurement</div>
        <div class="section-body">
            @if(!empty($d['logistics']))
            <div class="data-row">
                <div class="data-label">Logistics</div>
                <div class="data-value">{{ $d['logistics'] }}</div>
            </div>
            @endif
            @if(!empty($d['procurement_details']))
            <div class="data-row">
                <div class="data-label">Procurement Details</div>
                <div class="data-value">{{ $d['procurement_details'] }}</div>
            </div>
            @endif
            @if(!empty($d['supplier_name']) || !empty($d['supplier_contact']))
            <div class="two-col">
                <div class="col-half">
                    <div class="data-row">
                        <div class="data-label">Supplier Name</div>
                        <div class="data-value">{{ $d['supplier_name'] ?? '—' }}</div>
                    </div>
                </div>
                <div class="col-half">
                    <div class="data-row">
                        <div class="data-label">Supplier Contact</div>
                        <div class="data-value">{{ $d['supplier_contact'] ?? '—' }}</div>
                    </div>
                </div>
            </div>
            @endif
            @if(!empty($d['material_specifications']))
            <div class="data-row">
                <div class="data-label">Material Specifications</div>
                <div class="data-value">{{ $d['material_specifications'] }}</div>
            </div>
            @endif
            <div class="two-col">
                <div class="col-half">
                    <div class="data-row">
                        <div class="data-label">Quantity</div>
                        <div class="data-value">{{ $d['quantity'] ?? '—' }}</div>
                    </div>
                </div>
                <div class="col-half">
                    <div class="data-row">
                        <div class="data-label">Unit Price</div>
                        <div class="data-value">{{ isset($d['unit_price']) ? '$' . number_format($d['unit_price'], 2) : '—' }}</div>
                    </div>
                </div>
            </div>
            <div class="two-col">
                <div class="col-half">
                    <div class="data-row">
                        <div class="data-label">Budget</div>
                        <div class="data-value">{{ $workOrder->budget ? '$' . number_format($workOrder->budget, 2) : '—' }}</div>
                    </div>
                </div>
                <div class="col-half">
                    <div class="data-row">
                        <div class="data-label">Actual Cost</div>
                        <div class="data-value">{{ $workOrder->actual_cost ? '$' . number_format($workOrder->actual_cost, 2) : '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ─── PRODUCTION ─────────────────────────────────────────────────────── --}}
    @if(!empty($d['job_number']) || !empty($d['sign_type']) || !empty($d['job_description']))
    <div class="section">
        <div class="section-heading">Production</div>
        <div class="section-body">
            <div class="two-col">
                <div class="col-half">
                    <div class="data-row">
                        <div class="data-label">Job Number</div>
                        <div class="data-value">{{ $d['job_number'] ?? '—' }}</div>
                    </div>
                </div>
                <div class="col-half">
                    <div class="data-row">
                        <div class="data-label">Sign Type</div>
                        <div class="data-value">{{ $d['sign_type'] ?? '—' }}</div>
                    </div>
                </div>
            </div>
            <div class="two-col">
                <div class="col-half">
                    <div class="data-row">
                        <div class="data-label">Quantity</div>
                        <div class="data-value">{{ $d['production_quantity'] ?? '—' }}</div>
                    </div>
                </div>
                <div class="col-half">
                    <div class="data-row">
                        <div class="data-label">Size & Material</div>
                        <div class="data-value">{{ $d['size_and_material'] ?? '—' }}</div>
                    </div>
                </div>
            </div>
            @if(!empty($d['job_description']))
            <div class="data-row">
                <div class="data-label">Job Description</div>
                <div class="data-value">{{ $d['job_description'] }}</div>
            </div>
            @endif
            <div class="two-col">
                <div class="col-half">
                    <div class="data-row">
                        <div class="data-label">Colour Scheme</div>
                        <div class="data-value">{{ $d['colour_scheme'] ?? '—' }}</div>
                    </div>
                </div>
                <div class="col-half">
                    <div class="data-row">
                        <div class="data-label">Design File</div>
                        <div class="data-value">{{ $d['design_file'] ?? '—' }}</div>
                    </div>
                </div>
            </div>
            @if(!empty($d['finishing_requirements']))
            <div class="data-row">
                <div class="data-label">Finishing Requirements</div>
                <div class="data-value">{{ $d['finishing_requirements'] }}</div>
            </div>
            @endif
            @if(!empty($d['production_deadline']))
            <div class="data-row">
                <div class="data-label">Production Deadline</div>
                <div class="data-value">{{ \Carbon\Carbon::parse($d['production_deadline'])->format('d M Y') }}</div>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ─── DELIVERY & INSTALLATION ─────────────────────────────────────────── --}}
    @if(!empty($d['delivery_address']) || !empty($d['installation_requirements']) || !empty($d['date_of_job_completion']))
    <div class="section">
        <div class="section-heading">Delivery &amp; Installation</div>
        <div class="section-body">
            @if(!empty($d['delivery_address']))
            <div class="data-row">
                <div class="data-label">Delivery Address</div>
                <div class="data-value">{{ $d['delivery_address'] }}</div>
            </div>
            @endif
            @if(!empty($d['installation_requirements']))
            <div class="data-row">
                <div class="data-label">Installation Requirements</div>
                <div class="data-value">{{ $d['installation_requirements'] }}</div>
            </div>
            @endif
            <div class="two-col">
                <div class="col-half">
                    <div class="data-row">
                        <div class="data-label">Delivery Deadline</div>
                        <div class="data-value">{{ !empty($d['delivery_deadline']) ? \Carbon\Carbon::parse($d['delivery_deadline'])->format('d M Y') : '—' }}</div>
                    </div>
                </div>
                <div class="col-half">
                    <div class="data-row">
                        <div class="data-label">Date of Completion</div>
                        <div class="data-value">{{ !empty($d['date_of_job_completion']) ? \Carbon\Carbon::parse($d['date_of_job_completion'])->format('d M Y') : '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ─── ASSESSMENT / REPORT ─────────────────────────────────────────────── --}}
    @if(!empty($d['challenges']) || !empty($d['client_feedback']) || !empty($d['resolutions']))
    <div class="section">
        <div class="section-heading">Assessment / Report</div>
        <div class="section-body">
            @if(!empty($d['assessment_timeframe']))
            <div class="data-row">
                <div class="data-label">Timeframe</div>
                <div class="data-value">{{ $d['assessment_timeframe'] }}</div>
            </div>
            @endif
            @if(!empty($d['challenges']))
            <div class="data-row">
                <div class="data-label">Challenges</div>
                <div class="data-value">{{ $d['challenges'] }}</div>
            </div>
            @endif
            @if(!empty($d['client_feedback']))
            <div class="data-row">
                <div class="data-label">Client Feedback</div>
                <div class="data-value">{{ $d['client_feedback'] }}</div>
            </div>
            @endif
            @if(!empty($d['resolutions']))
            <div class="data-row">
                <div class="data-label">Resolutions</div>
                <div class="data-value">{{ $d['resolutions'] }}</div>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ─── SIGNATURES ──────────────────────────────────────────────────────── --}}
    <div class="section">
        <div class="section-heading">Authorisation &amp; Sign-Off</div>
        <div class="section-body" style="padding: 0;">
            <div class="sig-table">
                @foreach([1,2,3] as $i)
                <div class="sig-cell">
                    <div class="sig-label">Signatory {{ $i }}</div>
                    <div style="margin-bottom: 8px;">
                        <div class="sig-label">Name</div>
                        <div class="sig-value">{{ $d['signature_'.$i.'_name'] ?? '' ?: '' }}</div>
                    </div>
                    <div style="margin-bottom: 8px;">
                        <div class="sig-label">Signature</div>
                        <div class="sig-value">{{ $d['signature_'.$i.'_sign'] ?? '' ?: '' }}</div>
                    </div>
                    <div>
                        <div class="sig-label">Date &amp; Time</div>
                        <div class="sig-value">
                            @if(!empty($d['signature_'.$i.'_datetime']))
                                {{ \Carbon\Carbon::parse($d['signature_'.$i.'_datetime'])->format('d M Y H:i') }}
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ─── FOOTER ──────────────────────────────────────────────────────────── --}}
    <div class="footer">
        <div class="footer-left">HouseHold Brands (Pvt) Ltd</div>
        <div class="footer-right">{{ $generatedAt }}</div>
    </div>

</div>
</body>
</html>
