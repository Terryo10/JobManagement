<?php

namespace App\Filament\Staff\Widgets;

use App\Models\Task;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MyWorkloadWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $userId = auth()->id();

        $available = Task::whereNull('claimed_by')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();
        $claimed = Task::where('claimed_by', $userId)
            ->where('status', 'in_progress')
            ->count();
        $completed = Task::where('claimed_by', $userId)
            ->where('status', 'completed')
            ->whereMonth('completed_at', now()->month)
            ->count();
        $overdue = Task::where('claimed_by', $userId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->where('deadline', '<', now())
            ->count();

        return [
            Stat::make('Available Tasks', $available)
                ->description('Ready to claim')
                ->icon('heroicon-o-queue-list')
                ->color('primary')
                ->url(route('filament.staff.resources.tasks.index', ['tableFilters[queue][value]' => 'available'])),
            Stat::make('My Active Tasks', $claimed)
                ->description('Currently working')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->url(route('filament.staff.resources.tasks.index', ['tableFilters[queue][value]' => 'mine'])),
            Stat::make('Completed', $completed)
                ->description('This month')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->url(route('filament.staff.resources.tasks.index', ['tableFilters[status][value]' => 'completed'])),
            Stat::make('Overdue', $overdue)
                ->description('Needs attention')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($overdue > 0 ? 'danger' : 'success')
                ->url(route('filament.staff.resources.tasks.index', ['tableFilters[status][value]' => 'pending'])),
        ];
    }
}
