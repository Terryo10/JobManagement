<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Job Summary Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; margin: 20px; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .meta { font-size: 10px; color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1e3a5f; color: #fff; padding: 7px 6px; text-align: left; font-size: 10px; }
        td { padding: 6px; border-bottom: 1px solid #e2e8f0; font-size: 10px; vertical-align: top; }
        tr:nth-child(even) td { background: #f8fafc; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 9px; font-weight: bold; }
        .status-pending { background: #e2e8f0; color: #4a5568; }
        .status-in_progress { background: #fefcbf; color: #744210; }
        .status-completed { background: #c6f6d5; color: #22543d; }
        .status-cancelled { background: #fed7d7; color: #742a2a; }
        .status-on_hold { background: #bee3f8; color: #2a4365; }
        .footer { margin-top: 20px; font-size: 9px; color: #aaa; text-align: right; }
    </style>
</head>
<body>
    <h1>Job Summary Report</h1>
    <div class="meta">
        Generated: {{ $generatedAt }}
        @if($filterStatus) &nbsp;|&nbsp; Status: {{ ucfirst(str_replace('_', ' ', $filterStatus)) }} @endif
        @if($filterCategory) &nbsp;|&nbsp; Category: {{ ucfirst(str_replace('_', ' ', $filterCategory)) }} @endif
        &nbsp;|&nbsp; Total: {{ $records->count() }} job(s)
    </div>

    <table>
        <thead>
            <tr>
                <th>Ref #</th>
                <th>Title</th>
                <th>Client</th>
                <th>Dept</th>
                <th>Category</th>
                <th>Status</th>
                <th>Budget</th>
                <th>Actual Cost</th>
                <th>Tasks</th>
                <th>Done</th>
                <th>Deadline</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $record)
            <tr>
                <td>{{ $record->reference_number }}</td>
                <td>{{ \Str::limit($record->title, 30) }}</td>
                <td>{{ $record->client?->company_name ?? '—' }}</td>
                <td>{{ $record->assignedDepartment?->name ?? '—' }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $record->category ?? '')) }}</td>
                <td>
                    <span class="badge status-{{ $record->status }}">
                        {{ ucfirst(str_replace('_', ' ', $record->status ?? '')) }}
                    </span>
                </td>
                <td>{{ $record->budget ? '$' . number_format($record->budget, 2) : '—' }}</td>
                <td>{{ $record->actual_cost ? '$' . number_format($record->actual_cost, 2) : '—' }}</td>
                <td>{{ $record->tasks_count }}</td>
                <td>{{ $record->completed_tasks_count }}</td>
                <td>{{ $record->deadline?->format('d M Y') ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="11" style="text-align:center; color:#999;">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Household Media — Job Management System</div>
</body>
</html>
