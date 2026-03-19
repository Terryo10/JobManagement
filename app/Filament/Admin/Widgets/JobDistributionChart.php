<?php

namespace App\Filament\Admin\Widgets;

use App\Models\WorkOrder;
use Filament\Widgets\ChartWidget;

class JobDistributionChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = 'Job Distribution by Division';

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $categories = ['media' => 'Media', 'civil_works' => 'Civil Works', 'energy' => 'Energy', 'warehouse' => 'Warehouse'];
        $counts = [];
        foreach (array_keys($categories) as $cat) {
            $counts[] = WorkOrder::where('category', $cat)->whereNotIn('status', ['cancelled'])->count();
        }

        return [
            'datasets' => [
                [
                    'data' => $counts,
                    'backgroundColor' => ['#f59e0b', '#3b82f6', '#10b981', '#8b5cf6'],
                ],
            ],
            'labels' => array_values($categories),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
