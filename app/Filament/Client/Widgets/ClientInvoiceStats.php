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

        $outstanding = (clone $query)->whereIn('status', ['sent', 'overdue'])->sum('total');
        $overdue = (clone $query)->where('status', 'overdue')->count();
        $paid = (clone $query)->where('status', 'paid')->sum('total');

        return [
            Stat::make('Outstanding Balance', '$' . number_format($outstanding, 2))
                ->icon('heroicon-o-banknotes')
                ->color($outstanding > 0 ? 'warning' : 'success')
                ->description($overdue > 0 ? "{$overdue} overdue" : 'All up to date'),
            Stat::make('Total Paid', '$' . number_format($paid, 2))
                ->icon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}
