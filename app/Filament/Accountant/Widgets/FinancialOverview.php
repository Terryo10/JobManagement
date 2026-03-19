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

        // Invoice Stats
        $totalInvoiced = Invoice::whereIn('status', ['sent', 'signed', 'paid', 'approved', 'overdue'])
            ->sum('total');
        $outstanding = Invoice::whereIn('status', ['sent', 'signed', 'approved', 'overdue'])
            ->sum('total');
        $overdue = Invoice::where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->whereNotIn('status', ['paid', 'cancelled'])
                  ->whereNotNull('due_at')
                  ->where('due_at', '<', now());
            })
            ->sum('total');
        $paidThisMonth = Invoice::where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('total');

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

            Stat::make('Overdue Invoices', '$' . number_format($overdue, 2))
                ->description('Action required on late payments')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color($overdue > 0 ? 'danger' : 'success')
                ->url('/accountant/invoices?tableFilters[status][value]=overdue'),

            // Row 2: Financial Summary
            Stat::make('Total Invoiced', '$' . number_format($totalInvoiced, 2))
                ->description('Life-to-date active invoices')
                ->descriptionIcon('heroicon-o-document-currency-dollar')
                ->color('primary'),

            Stat::make('Outstanding Balance', '$' . number_format($outstanding, 2))
                ->description('Pending collection')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('warning'),

            Stat::make('Paid This Month', '$' . number_format($paidThisMonth, 2))
                ->description(now()->format('F Y'))
                ->descriptionIcon('heroicon-o-check-badge')
                ->color('success'),
        ];
    }
}
