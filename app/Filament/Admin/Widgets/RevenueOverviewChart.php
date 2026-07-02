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
        $dates = collect();
        for ($i = 5; $i >= 0; $i--) {
            $dates->push(now()->subMonths($i));
        }

        $currencies = Invoice::where('status', 'paid')->distinct()->pluck('currency')->filter()->values();
        if ($currencies->isEmpty()) {
            $currencies = collect(['USD']);
        }

        $palette = [
            ['#f59e0b', 'rgba(245, 158, 11, 0.1)'],
            ['#3b82f6', 'rgba(59, 130, 246, 0.1)'],
            ['#10b981', 'rgba(16, 185, 129, 0.1)'],
            ['#ef4444', 'rgba(239, 68, 68, 0.1)'],
        ];

        $datasets = $currencies->values()->map(function ($currency, $index) use ($dates, $palette) {
            [$borderColor, $backgroundColor] = $palette[$index % count($palette)];

            $data = $dates->map(fn ($date) => Invoice::where('status', 'paid')
                ->where('currency', $currency)
                ->whereMonth('paid_at', $date->month)
                ->whereYear('paid_at', $date->year)
                ->sum('total'));

            return [
                'label' => "Revenue ({$currency})",
                'data' => $data->toArray(),
                'borderColor' => $borderColor,
                'backgroundColor' => $backgroundColor,
                'fill' => true,
                'tension' => 0.3,
            ];
        });

        return [
            'datasets' => $datasets->toArray(),
            'labels' => $dates->map(fn ($date) => $date->format('M Y'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
