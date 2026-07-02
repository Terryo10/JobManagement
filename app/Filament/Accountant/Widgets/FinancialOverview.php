<?php

namespace App\Filament\Accountant\Widgets;

use App\Models\Invoice;
use App\Models\PurchaseOrder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinancialOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // Requisition Stats
        $pendingPo = PurchaseOrder::where('status', 'pending_finance_approval')->count();
        $financeApprovedPo = PurchaseOrder::where('status', 'finance_approved')->count();

        // Invoice Stats — grouped by currency so USD and ZWG figures aren't summed together
        $byCurrency = fn ($query) => $query->selectRaw('currency, sum(total) as total')->groupBy('currency')->pluck('total', 'currency');
        $display = fn ($amounts) => $amounts->isEmpty()
            ? '$0.00'
            : $amounts->map(fn ($total, $currency) => ($currency ?: 'USD') . ' ' . number_format($total, 2))->implode(' / ');

        $totalInvoiced = $display($byCurrency(Invoice::whereIn('status', ['sent', 'signed', 'paid', 'approved', 'overdue'])));
        $outstanding = $display($byCurrency(Invoice::whereIn('status', ['sent', 'signed', 'approved', 'overdue'])));
        $overdueAmounts = $byCurrency(Invoice::where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->whereNotIn('status', ['paid', 'cancelled'])
                  ->whereNotNull('due_at')
                  ->where('due_at', '<', now());
            }));
        $overdue = $display($overdueAmounts);
        $paidThisMonth = $display($byCurrency(Invoice::where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)));

        return [
            // Row 1: Action Items
            Stat::make('Awaiting Your Approval', $pendingPo)
                ->description($pendingPo > 0 ? 'Requisitions needing review' : 'All clear!')
                ->descriptionIcon($pendingPo > 0 ? 'heroicon-o-exclamation-circle' : 'heroicon-o-check-circle')
                ->color($pendingPo > 0 ? 'warning' : 'success')
                ->url('/accountant/purchase-orders?tableFilters[status][value]=pending_finance_approval'),

            Stat::make('Finance Approved', $financeApprovedPo)
                ->description('Pending final admin sign-off')
                ->descriptionIcon('heroicon-o-clock')
                ->color('info'),

            Stat::make('Overdue Invoices', $overdue)
                ->description('Action required on late payments')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color($overdueAmounts->isNotEmpty() ? 'danger' : 'success')
                ->url('/accountant/invoices?tableFilters[status][value]=overdue'),

            // Row 2: Financial Summary
            Stat::make('Total Invoiced', $totalInvoiced)
                ->description('Life-to-date active invoices')
                ->descriptionIcon('heroicon-o-document-currency-dollar')
                ->color('primary'),

            Stat::make('Outstanding Balance', $outstanding)
                ->description('Pending collection')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('warning'),

            Stat::make('Paid This Month', $paidThisMonth)
                ->description(now()->format('F Y'))
                ->descriptionIcon('heroicon-o-check-badge')
                ->color('success'),
        ];
    }
}
