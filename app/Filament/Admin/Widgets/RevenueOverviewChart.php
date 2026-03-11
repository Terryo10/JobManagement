<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Invoice;
use Filament\Widgets\ChartWidget;

class RevenueOverviewChart extends ChartWidget
{
    protected static ?int $sort = 5;
    protected static ?string $heading = 'Revenue Trend (Last 6 Months)';
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $months = collect();
        $revenues = collect();

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months->push($date->format('M Y'));
            $revenues->push(
                Invoice::where('status', 'paid')
                    ->whereMonth('paid_at', $date->month)
                    ->whereYear('paid_at', $date->year)
                    ->sum('total')
            );
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue ($)',
                    'data' => $revenues->toArray(),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $months->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
