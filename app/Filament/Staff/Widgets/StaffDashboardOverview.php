<?php

namespace App\Filament\Staff\Widgets;

use App\Models\PurchaseOrder;
use App\Models\Task;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StaffDashboardOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $userId = auth()->id();

        // Task Stats
        $availableTasks = Task::whereNull('claimed_by')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();
        $myActiveTasks = Task::where('claimed_by', $userId)
            ->where('status', 'in_progress')
            ->count();
        $overdueTasks = Task::where('claimed_by', $userId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->where('deadline', '<', now())
            ->count();

        // Requisition Stats
        $myPendingRequisitions = PurchaseOrder::where('ordered_by', $userId)
            ->whereIn('status', ['pending_finance_approval', 'finance_approved'])
            ->count();
        $myApprovedThisMonth = PurchaseOrder::where('ordered_by', $userId)
            ->where('status', 'approved')
            ->whereMonth('created_at', now()->month)
            ->count();
            
        return [
            // Row 1: Tasks
            Stat::make('Available Tasks', $availableTasks)
                ->description('Ready to claim')
                ->descriptionIcon('heroicon-o-queue-list')
                ->color('primary')
                ->url('/staff/tasks?tableFilters[queue][value]=available'),

            Stat::make('My Active Tasks', $myActiveTasks)
                ->description('Currently working')
                ->descriptionIcon('heroicon-o-arrow-path')
                ->color('warning')
                ->url('/staff/tasks?tableFilters[queue][value]=mine'),

            Stat::make('Overdue Tasks', $overdueTasks)
                ->description($overdueTasks > 0 ? 'Needs attention' : 'All clear!')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($overdueTasks > 0 ? 'danger' : 'success'),

            // Row 2: Requisitions
            Stat::make('My Pending Requisitions', $myPendingRequisitions)
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning')
                ->url('/staff/purchase-orders'),

            Stat::make('Approved This Month', $myApprovedThisMonth)
                ->description('Requisitions cleared')
                ->descriptionIcon('heroicon-o-check-badge')
                ->color('success'),
        ];
    }
}
