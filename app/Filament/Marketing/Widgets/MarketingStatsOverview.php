<?php

namespace App\Filament\Marketing\Widgets;

use App\Models\Lead;
use App\Models\Proposal;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MarketingStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $activeLeads = Lead::whereIn('status', ['new', 'in_progress'])->count();
        $convertedLeads = Lead::where('status', 'converted')
            ->whereMonth('converted_at', now()->month)
            ->whereYear('converted_at', now()->year)
            ->count();

        $openProposals = Proposal::whereIn('status', ['draft', 'submitted'])->count();
        $openProposalsValue = Proposal::whereIn('status', ['draft', 'submitted'])->sum('value');

        return [
            Stat::make('Active Leads', $activeLeads)
                ->description('New & In Progress')
                ->descriptionIcon('heroicon-m-funnel')
                ->color('primary')
                ->url(route('filament.marketing.resources.leads.index', ['tableFilters[status][value]' => 'new'])),

            Stat::make('Converted This Month', $convertedLeads)
                ->description('Successfully closed')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->url(route('filament.marketing.resources.leads.index', ['tableFilters[status][value]' => 'converted'])),

            Stat::make('Open Proposals', $openProposals)
                ->description('Value: $' . number_format($openProposalsValue, 2))
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning')
                ->url(route('filament.marketing.resources.proposals.index', ['tableFilters[status][value]' => 'submitted'])),
        ];
    }
}
