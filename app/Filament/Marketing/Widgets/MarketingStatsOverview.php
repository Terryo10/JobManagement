<?php

namespace App\Filament\Marketing\Widgets;

use App\Models\Lead;
use App\Models\Proposal;
use App\Models\WorkOrder;
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
        // Grouped by currency so USD and ZWG proposal values aren't summed together
        $openProposalsByCurrency = Proposal::whereIn('status', ['draft', 'submitted'])
            ->selectRaw('currency, sum(value) as value')
            ->groupBy('currency')
            ->pluck('value', 'currency');

        $openProposalsValue = $openProposalsByCurrency->isEmpty()
            ? '$0.00'
            : $openProposalsByCurrency->map(fn ($value, $currency) => ($currency ?: 'USD').' '.number_format($value, 2))->implode(' / ');

        $activeJobCards = WorkOrder::whereIn('status', ['pending', 'in_progress', 'on_hold'])->count();

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
                ->description('Value: '.$openProposalsValue)
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning')
                ->url(route('filament.marketing.resources.proposals.index', ['tableFilters[status][value]' => 'submitted'])),

            Stat::make('Active Job Cards', $activeJobCards)
                ->description('Pending, in progress & on hold')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('info')
                ->url(route('filament.marketing.resources.work-orders.index')),
        ];
    }
}
