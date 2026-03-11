<?php

namespace App\Services;

use App\Models\Task;
use App\Models\WorkOrder;
use App\Models\Invoice;
use App\Models\Material;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Job Summary report — work orders with task completion and budget usage.
     */
    public function jobSummary(?string $status = null, ?string $category = null, ?string $dateFrom = null, ?string $dateTo = null): Collection
    {
        $query = WorkOrder::query()
            ->withCount('tasks')
            ->withCount(['tasks as completed_tasks_count' => fn ($q) => $q->where('status', 'completed')])
            ->with(['client:id,company_name', 'assignedDepartment:id,name']);

        if ($status) $query->where('status', $status);
        if ($category) $query->where('category', $category);
        if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);

        return $query->orderBy('created_at', 'desc')->get()->map(fn ($wo) => [
            'reference' => $wo->reference_number,
            'title' => $wo->title,
            'client' => $wo->client?->company_name ?? '—',
            'department' => $wo->assignedDepartment?->name ?? '—',
            'category' => $wo->category,
            'status' => $wo->status,
            'priority' => $wo->priority,
            'budget' => $wo->budget,
            'actual_cost' => $wo->actual_cost,
            'budget_usage' => $wo->budget ? round(($wo->actual_cost / $wo->budget) * 100) . '%' : '—',
            'tasks' => "{$wo->completed_tasks_count}/{$wo->tasks_count}",
            'deadline' => $wo->deadline?->format('Y-m-d'),
            'created' => $wo->created_at->format('Y-m-d'),
        ]);
    }

    /**
     * Staff Performance — tasks completed, hours worked, per user.
     */
    public function staffPerformance(?string $dateFrom = null, ?string $dateTo = null): Collection
    {
        $query = Task::query()
            ->select('assigned_to')
            ->selectRaw('COUNT(*) as total_tasks')
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tasks")
            ->selectRaw('COALESCE(SUM(actual_hours), 0) as total_hours')
            ->selectRaw('COALESCE(SUM(estimated_hours), 0) as estimated_hours')
            ->whereNotNull('assigned_to')
            ->groupBy('assigned_to')
            ->with('assignedTo:id,name');

        if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);

        return $query->get()->map(fn ($row) => [
            'staff' => $row->assignedTo?->name ?? '—',
            'total_tasks' => $row->total_tasks,
            'completed' => $row->completed_tasks,
            'completion_rate' => $row->total_tasks > 0 ? round(($row->completed_tasks / $row->total_tasks) * 100) . '%' : '0%',
            'actual_hours' => number_format($row->total_hours, 1),
            'estimated_hours' => number_format($row->estimated_hours, 1),
            'efficiency' => $row->estimated_hours > 0 ? round(($row->total_hours / $row->estimated_hours) * 100) . '%' : '—',
        ]);
    }

    /**
     * Revenue Overview — invoices by status and by month.
     */
    public function revenueOverview(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = Invoice::query();
        if ($dateFrom) $query->whereDate('issued_at', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('issued_at', '<=', $dateTo);

        $byStatus = (clone $query)->select('status')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('COALESCE(SUM(total), 0) as total')
            ->groupBy('status')->get();

        $monthly = Invoice::whereNotIn('status', ['cancelled', 'draft'])
            ->selectRaw("DATE_FORMAT(issued_at, '%Y-%m') as month")
            ->selectRaw('COALESCE(SUM(total), 0) as revenue')
            ->groupByRaw("DATE_FORMAT(issued_at, '%Y-%m')")
            ->orderBy('month')
            ->limit(12)
            ->get();

        return [
            'by_status' => $byStatus,
            'monthly' => $monthly,
            'total_revenue' => $byStatus->where('status', 'paid')->sum('total'),
            'outstanding' => $byStatus->whereIn('status', ['sent', 'overdue'])->sum('total'),
        ];
    }

    /**
     * Material Usage — materials consumed across work orders.
     */
    public function materialUsage(): Collection
    {
        return Material::where('is_active', true)
            ->with('stockLevel')
            ->get()
            ->map(fn ($m) => [
                'name' => $m->name,
                'sku' => $m->sku,
                'category' => $m->category,
                'current_stock' => $m->stockLevel?->current_quantity ?? 0,
                'minimum' => $m->minimum_stock_level,
                'unit_cost' => $m->unit_cost,
                'status' => ($m->stockLevel?->current_quantity ?? 0) <= ($m->minimum_stock_level ?? 0) ? 'LOW' : 'OK',
            ]);
    }
}
