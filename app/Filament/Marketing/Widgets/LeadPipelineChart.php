<?php

namespace App\Filament\Marketing\Widgets;

use App\Models\Lead;
use Filament\Widgets\ChartWidget;

class LeadPipelineChart extends ChartWidget
{
    protected static ?string $heading = 'Lead Pipeline';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $statuses = ['new' => 'New', 'in_progress' => 'In Progress', 'converted' => 'Converted', 'lost' => 'Lost'];
        $counts = [];
        $colors = [
            'new' => '#3b82f6', // blue
            'in_progress' => '#f59e0b', // amber
            'converted' => '#10b981', // emerald
            'lost' => '#ef4444', // red
        ];

        $countsData = [];
        $backgroundColors = [];

        foreach ($statuses as $status => $label) {
            $countsData[] = Lead::where('status', $status)->count();
            $backgroundColors[] = $colors[$status];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Leads',
                    'data' => $countsData,
                    'backgroundColor' => $backgroundColors,
                ],
            ],
            'labels' => array_values($statuses),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
