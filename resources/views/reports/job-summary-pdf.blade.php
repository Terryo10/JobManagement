<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job Summary Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #222; font-size: 10.5px; line-height: 1.4; }

        .page { padding: 20px 28px; }

        /* ── Header ── */
        .header { border: 2px solid #cc0000; margin-bottom: 16px; }
        .header-top { display: table; width: 100%; }
        .header-left { display: table-cell; width: 55%; vertical-align: middle; padding: 14px 18px; border-right: 2px solid #cc0000; }
        .header-right { display: table-cell; width: 45%; vertical-align: middle; padding: 14px 18px; }

        .company-name { font-size: 15px; font-weight: 900; color: #000; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px; }
        .company-sub { font-size: 9px; color: #555; }
        .doc-type { font-size: 18px; font-weight: 900; color: #cc0000; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px; }
        .detail-line { margin-bottom: 2px; font-size: 9.5px; color: #444; }
        .detail-label { font-weight: 700; color: #222; }

        /* ── Summary Bar ── */
        .summary-bar { display: table; width: 100%; margin-bottom: 14px; border: 1px solid #ccc; }
        .summary-item { display: table-cell; text-align: center; padding: 8px 6px; border-right: 1px solid #ccc; }
        .summary-item:last-child { border-right: none; }
        .summary-number { font-size: 18px; font-weight: 900; color: #cc0000; }
        .summary-label { font-size: 8px; font-weight: 700; text-transform: uppercase; color: #777; letter-spacing: 0.05em; }

        /* ── Table ── */
        table.report-table { width: 100%; border-collapse: collapse; border: 1px solid #ccc; }
        table.report-table th {
            background: #2d2d2d; color: #fff; padding: 6px 8px;
            font-size: 8.5px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.07em; text-align: left; border-right: 1px solid #444;
        }
        table.report-table th:last-child { border-right: none; }
        table.report-table td {
            padding: 5px 8px; border-bottom: 1px solid #e5e5e5;
            font-size: 9.5px; vertical-align: middle; border-right: 1px solid #eee;
        }
        table.report-table td:last-child { border-right: none; }
        table.report-table tbody tr:nth-child(even) td { background: #f9f9f9; }
        table.report-table tbody tr:hover td { background: #f0f4ff; }

        /* Numeric columns */
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* ── Badges ── */
        .badge { display: inline-block; padding: 1px 6px; border-radius: 3px; font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap; }
        .badge-gray    { background: #e5e7eb; color: #374151; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info    { background: #dbeafe; color: #1e40af; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger  { background: #fee2e2; color: #991b1b; }

        /* Category badges */
        .cat-media      { background: #dbeafe; color: #1e40af; }
        .cat-civil      { background: #fef3c7; color: #92400e; }
        .cat-energy     { background: #d1fae5; color: #065f46; }
        .cat-warehouse  { background: #e0e7ff; color: #3730a3; }

        /* ── Footer ── */
        .footer { margin-top: 16px; border-top: 2px solid #cc0000; padding-top: 6px; display: table; width: 100%; }
        .footer-left  { display: table-cell; width: 60%; font-size: 8.5px; color: #777; }
        .footer-right { display: table-cell; width: 40%; text-align: right; font-size: 8.5px; color: #777; }

        /* ── Empty state ── */
        .empty-state { text-align: center; padding: 40px; color: #999; font-size: 12px; border: 1px solid #e5e5e5; }
    </style>
</head>
<body>
<div class="page">

    {{-- ─── HEADER ─────────────────────────────────────────────────────────── --}}
    <div class="header">
        <div class="header-top">
            <div class="header-left">
                <div style="margin-bottom: 6px;">
                    @if(file_exists(public_path('images/logo.png')))
                        <img src="{{ public_path('images/logo.png') }}" alt="Household Brands" style="max-width: 100px; height: auto;">
                    @elseif(file_exists(public_path('images/logo.jpg')))
                        <img src="{{ public_path('images/logo.jpg') }}" alt="Household Brands" style="max-width: 100px; height: auto;">
                    @else
                        <div style="font-family: 'Arial Black', Arial, sans-serif; font-size: 22px; font-weight: 900; color: #000; padding: 8px 0;">HOUSEHOLD</div>
                    @endif
                </div>
                <div class="company-name">HouseHold Brands (Pvt) Ltd</div>
                <div class="company-sub">Job Management System</div>
            </div>
            <div class="header-right">
                <div class="doc-type">Job Summary</div>
                <div class="detail-line"><span class="detail-label">Generated:</span> {{ $generatedAt }}</div>
                @if($filterStatus)
                    <div class="detail-line"><span class="detail-label">Status Filter:</span> {{ ucfirst(str_replace('_', ' ', $filterStatus)) }}</div>
                @endif
                @if($filterCategory)
                    <div class="detail-line"><span class="detail-label">Category Filter:</span> {{ ucfirst(str_replace('_', ' ', $filterCategory)) }}</div>
                @endif
                <div class="detail-line"><span class="detail-label">Total Jobs:</span> {{ $records->count() }}</div>
            </div>
        </div>
    </div>

    {{-- ─── SUMMARY STATS ─────────────────────────────────────────────────── --}}
    @php
        $totalBudget = $records->sum('budget');
        $totalActual = $records->sum('actual_cost');
        $totalTasks = $records->sum('tasks_count');
        $totalDone = $records->sum('completed_tasks_count');
        $completedJobs = $records->where('status', 'completed')->count();
        $inProgressJobs = $records->where('status', 'in_progress')->count();
    @endphp
    <div class="summary-bar">
        <div class="summary-item">
            <div class="summary-number">{{ $records->count() }}</div>
            <div class="summary-label">Total Jobs</div>
        </div>
        <div class="summary-item">
            <div class="summary-number">{{ $inProgressJobs }}</div>
            <div class="summary-label">In Progress</div>
        </div>
        <div class="summary-item">
            <div class="summary-number">{{ $completedJobs }}</div>
            <div class="summary-label">Completed</div>
        </div>
        <div class="summary-item">
            <div class="summary-number">${{ number_format($totalBudget, 0) }}</div>
            <div class="summary-label">Total Budget</div>
        </div>
        <div class="summary-item">
            <div class="summary-number">${{ number_format($totalActual, 0) }}</div>
            <div class="summary-label">Total Actual</div>
        </div>
        <div class="summary-item">
            <div class="summary-number">{{ $totalTasks }}</div>
            <div class="summary-label">Tasks</div>
        </div>
    </div>

    {{-- ─── DATA TABLE ─────────────────────────────────────────────────────── --}}
    @if($records->count() > 0)
    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 10%;">Ref #</th>
                <th style="width: 18%;">Title</th>
                <th style="width: 12%;">Client</th>
                <th style="width: 10%;">Dept</th>
                <th style="width: 8%;">Category</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 9%;">Budget</th>
                <th style="width: 9%;">Actual</th>
                <th style="width: 5%;">Tasks</th>
                <th style="width: 5%;">Done</th>
                <th style="width: 8%;">Deadline</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
            <tr>
                <td style="font-weight: 600;">{{ $record->reference_number }}</td>
                <td>{{ \Str::limit($record->title, 28) }}</td>
                <td>{{ \Str::limit($record->client?->company_name ?? '—', 18) }}</td>
                <td>{{ \Str::limit($record->assignedDepartment?->name ?? '—', 14) }}</td>
                <td>
                    @php
                        $catClass = match($record->category) {
                            'media' => 'cat-media', 'civil_works' => 'cat-civil',
                            'energy' => 'cat-energy', 'warehouse' => 'cat-warehouse',
                            default => 'badge-gray',
                        };
                    @endphp
                    <span class="badge {{ $catClass }}">{{ ucfirst(str_replace('_', ' ', $record->category ?? '')) }}</span>
                </td>
                <td>
                    @php
                        $statusClass = match($record->status) {
                            'pending' => 'badge-gray', 'in_progress' => 'badge-warning',
                            'completed' => 'badge-success', 'cancelled' => 'badge-danger',
                            'on_hold' => 'badge-info', default => 'badge-gray',
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $record->status ?? '')) }}</span>
                </td>
                <td class="text-right">{{ $record->budget ? '$' . number_format($record->budget, 2) : '—' }}</td>
                <td class="text-right">{{ $record->actual_cost ? '$' . number_format($record->actual_cost, 2) : '—' }}</td>
                <td class="text-center">{{ $record->tasks_count }}</td>
                <td class="text-center">{{ $record->completed_tasks_count }}</td>
                <td>{{ $record->deadline?->format('d M Y') ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state">No records match the selected filters.</div>
    @endif

    {{-- ─── FOOTER ──────────────────────────────────────────────────────────── --}}
    <div class="footer">
        <div class="footer-left">HouseHold Brands (Pvt) Ltd &mdash; Confidential</div>
        <div class="footer-right">{{ $generatedAt }}</div>
    </div>

</div>
</body>
</html>
