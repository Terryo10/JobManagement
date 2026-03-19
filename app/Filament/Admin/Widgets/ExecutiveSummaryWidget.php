<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\Task;
use App\Models\WorkOrder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ExecutiveSummaryWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // 1. Awaiting Your Approval (from AdminFinancialOverview)
        $awaitingAdmin = PurchaseOrder::where('status', 'finance_approved')->count();

        // 2. Urgent Attention (from JobStatsOverview)
        $urgentItems = WorkOrder::where('status', '!=', 'completed')
            ->where(function ($q) {
                $q->where('priority', 'urgent')
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('deadline')->where('deadline', '<', now());
                  });
            })->count();
        $overdueTasks = Task::where('deadline', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        // 3. Active Jobs (from JobStatsOverview)
        $totalActive = WorkOrder::whereNotIn('status', ['completed', 'cancelled'])->count();
        $inProgress = WorkOrder::where('status', 'in_progress')->count();

        // 4. Outstanding Balance (from AdminFinancialOverview)
        $outstanding = Invoice::whereIn('status', ['sent', 'signed', 'approved', 'overdue'])
            ->sum('total');

        return [
            Stat::make('Awaiting Your Approval', $awaitingAdmin)
                ->description($awaitingAdmin > 0 ? 'Needs your final sign-off' : 'All clear!')
                ->descriptionIcon($awaitingAdmin > 0 ? 'heroicon-o-exclamation-circle' : 'heroicon-o-check-circle')
                ->color($awaitingAdmin > 0 ? 'warning' : 'success')
                ->url('/admin/purchase-orders?tableFilters[status][value]=finance_approved'),

            Stat::make('Urgent Attention', $urgentItems)
                ->description($overdueTasks . ' overdue tasks')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($urgentItems > 0 ? 'danger' : 'success')
                ->url(route('filament.admin.resources.work-orders.index', ['tableFilters[priority][value]' => 'urgent'])),

            Stat::make('Active Jobs', $totalActive)
                ->description($inProgress . ' in progress')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, $totalActive])
                ->url(route('filament.admin.resources.work-orders.index')),

            Stat::make('Outstanding Balance', '$' . number_format($outstanding, 2))
                ->description('Unpaid collection')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('warning')
                ->url('/admin/invoices'),
        ];
    }
}
