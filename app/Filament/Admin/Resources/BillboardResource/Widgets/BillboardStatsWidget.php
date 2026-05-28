<?php

namespace App\Filament\Admin\Resources\BillboardResource\Widgets;

use App\Models\Billboard;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BillboardStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $total = Billboard::count();
        $static = Billboard::where('type', 'static')->count();
        $transit = Billboard::whereIn('type', ['bus', 'kombi'])->count();
        
        $occupied = Billboard::where('status', 'occupied')->count();
        $occupancyRate = $total > 0 ? round(($occupied / $total) * 100) : 0;

        return [
            Stat::make('Total Installed Assets', $total)
                ->description('All billboards & transit media')
                ->descriptionIcon('heroicon-o-presentation-chart-bar')
                ->color('primary'),

            Stat::make('Static Billboards', $static)
                ->description('Fixed location signs')
                ->descriptionIcon('heroicon-o-map-pin')
                ->color('info'),

            Stat::make('Transit Media', $transit)
                ->description('Buses & Kombies active')
                ->descriptionIcon('heroicon-o-truck')
                ->color('warning'),

            Stat::make('Occupancy Rate', "{$occupancyRate}%")
                ->description("{$occupied} of {$total} occupied")
                ->descriptionIcon('heroicon-o-check-badge')
                ->color($occupancyRate > 50 ? 'success' : 'warning'),
        ];
    }
}
