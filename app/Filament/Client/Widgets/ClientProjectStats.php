<?php

namespace App\Filament\Client\Widgets;

use App\Models\WorkOrder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ClientProjectStats extends BaseWidget
{
    protected function getStats(): array
    {
        $email = auth()->user()?->email;

        $query = WorkOrder::whereHas('client', fn ($q) => $q->where('email', $email));

        $total = (clone $query)->count();
        $active = (clone $query)->whereIn('status', ['pending', 'in_progress', 'on_hold'])->count();
        $completed = (clone $query)->where('status', 'completed')->count();

        return [
            Stat::make('Total Projects', $total)
                ->icon('heroicon-o-clipboard-document-list')
                ->color('primary'),
            Stat::make('Active Projects', $active)
                ->icon('heroicon-o-play-circle')
                ->color('warning')
                ->description('In progress or pending'),
            Stat::make('Completed', $completed)
                ->icon('heroicon-o-check-badge')
                ->color('success'),
        ];
    }
}
