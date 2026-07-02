<?php

namespace App\Filament\Client\Widgets;

use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ClientInvoiceStats extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $email = auth()->user()?->email;

        $query = Invoice::whereHas('client', fn ($q) => $q->where('email', $email));

        $byCurrency = fn ($query) => $query->selectRaw('currency, sum(total) as total')->groupBy('currency')->pluck('total', 'currency');
        $display = fn ($amounts) => $amounts->isEmpty()
            ? '$0.00'
            : $amounts->map(fn ($total, $currency) => ($currency ?: 'USD') . ' ' . number_format($total, 2))->implode(' / ');

        $outstandingAmounts = $byCurrency((clone $query)->whereIn('status', ['sent', 'overdue']));
        $overdue = (clone $query)->where('status', 'overdue')->count();
        $paidAmounts = $byCurrency((clone $query)->where('status', 'paid'));

        return [
            Stat::make('Outstanding Balance', $display($outstandingAmounts))
                ->icon('heroicon-o-banknotes')
                ->color($outstandingAmounts->isNotEmpty() ? 'warning' : 'success')
                ->description($overdue > 0 ? "{$overdue} overdue" : 'All up to date'),
            Stat::make('Total Paid', $display($paidAmounts))
                ->icon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}
