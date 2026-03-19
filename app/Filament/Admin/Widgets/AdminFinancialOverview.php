<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Invoice;
use App\Models\PurchaseOrder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminFinancialOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // Requisition Stats
        $awaitingAdmin = PurchaseOrder::where('status', 'finance_approved')->count();
        $pendingFinance = PurchaseOrder::where('status', 'pending_finance_approval')->count();
        $approvedMonth = PurchaseOrder::where('status', 'approved')
            ->whereMonth('created_at', now()->month)
            ->count();

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

        return [
            // Row 1: Action Items
            Stat::make('Awaiting Your Approval', $awaitingAdmin)
                ->description($awaitingAdmin > 0 ? 'Needs your final sign-off' : 'All clear!')
                ->descriptionIcon($awaitingAdmin > 0 ? 'heroicon-o-exclamation-circle' : 'heroicon-o-check-circle')
                ->color($awaitingAdmin > 0 ? 'warning' : 'success')
                ->url('/admin/purchase-orders?tableFilters[status][value]=finance_approved'),

            Stat::make('Pending Finance Review', $pendingFinance)
                ->description('Staff requests in pipeline')
                ->descriptionIcon('heroicon-o-clock')
                ->color('gray'),

            Stat::make('Approved This Month', $approvedMonth)
                ->description('Requisitions cleared')
                ->descriptionIcon('heroicon-o-check-badge')
                ->color('success'),

            // Row 2: Financial Summary
            Stat::make('Total Invoiced', '$' . number_format($totalInvoiced, 2))
                ->description('Active invoices')
                ->descriptionIcon('heroicon-o-document-currency-dollar')
                ->color('primary'),

            Stat::make('Outstanding Balance', '$' . number_format($outstanding, 2))
                ->description('Unpaid collection')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('warning'),

            Stat::make('Overdue Revenue', '$' . number_format($overdue, 2))
                ->description('Late payments')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($overdue > 0 ? 'danger' : 'success')
                ->url('/admin/invoices?tableFilters[status][value]=overdue'),
        ];
    }
}
