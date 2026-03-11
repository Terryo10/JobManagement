<?php
namespace App\Filament\Accountant\Widgets;

use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RevenueStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalInvoiced = Invoice::whereNotIn('status', ['draft', 'cancelled'])->sum('total');
        $totalPaid = Invoice::where('status', 'paid')->sum('total');
        $outstanding = Invoice::whereIn('status', ['sent', 'overdue'])->sum('total');
        $overdueCount = Invoice::where('status', 'overdue')->count();

        return [
            Stat::make('Total Invoiced', '$' . number_format($totalInvoiced, 2))
                ->icon('heroicon-o-banknotes')
                ->color('primary')
                ->url(route('filament.accountant.resources.invoices.index')),
            Stat::make('Total Received', '$' . number_format($totalPaid, 2))
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->url(route('filament.accountant.resources.invoices.index', ['tableFilters[status][value]' => 'paid'])),
            Stat::make('Outstanding Balance', '$' . number_format($outstanding, 2))
                ->description($overdueCount > 0 ? "{$overdueCount} overdue invoices" : 'No overdue invoices')
                ->icon('heroicon-o-exclamation-circle')
                ->color($overdueCount > 0 ? 'danger' : 'warning')
                ->url(route('filament.accountant.resources.invoices.index', ['tableFilters[status][value]' => 'overdue'])),
        ];
    }
}
