<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Task;
use App\Models\WorkOrder;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class JobStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalActive = WorkOrder::whereNotIn('status', ['completed', 'cancelled'])->count();
        $inProgress = WorkOrder::where('status', 'in_progress')->count();
        $completedThisMonth = WorkOrder::where('status', 'completed')
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->count();

        $overdueTasks = Task::where('deadline', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $urgentItems = WorkOrder::where('status', '!=', 'completed')
            ->where(function ($q) {
                $q->where('priority', 'urgent')
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('deadline')->where('deadline', '<', now());
                  });
            })->count();

        $pendingInvoiceTotal = \App\Models\Invoice::whereIn('status', ['sent', 'overdue'])
            ->sum('total');

        return [
            Stat::make('Active Jobs', $totalActive)
                ->description($inProgress . ' in progress')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, $totalActive]),
            Stat::make('Urgent Attention', $urgentItems)
                ->description($overdueTasks . ' overdue tasks')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
            Stat::make('Pending Invoices', '$' . number_format($pendingInvoiceTotal, 2))
                ->description('Awaiting payment')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
            Stat::make('Completed This Month', $completedThisMonth)
                ->description('Jobs finished')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
        ];
    }
}
