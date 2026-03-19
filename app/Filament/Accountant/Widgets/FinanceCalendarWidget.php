<?php

namespace App\Filament\Accountant\Widgets;

use App\Models\Invoice;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class FinanceCalendarWidget extends FullCalendarWidget
{
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    public \Illuminate\Database\Eloquent\Model | string | null $model = Invoice::class;

    public function fetchEvents(array $fetchInfo): array
    {
        $start = $fetchInfo['start'];
        $end   = $fetchInfo['end'];

        $events = [];

        // Invoice due dates
        $invoices = Invoice::query()
            ->whereNotIn('status', ['cancelled', 'paid'])
            ->whereNotNull('due_at')
            ->whereBetween('due_at', [$start, $end])
            ->get();

        foreach ($invoices as $invoice) {
            $isOverdue = $invoice->due_at->isPast();
            $events[] = [
                'id'              => 'inv-' . $invoice->id,
                'title'           => '📄 ' . ($invoice->client?->company_name ?? 'Invoice') . ' — $' . number_format($invoice->total, 0),
                'start'           => $invoice->due_at->toDateString(),
                'color'           => $isOverdue ? '#ef4444' : '#f59e0b',
                'textColor'       => '#fff',
                'url'             => '/accountant/invoices/' . $invoice->id,
                'extendedProps'   => ['type' => 'invoice_due'],
            ];
        }

        // Invoice issue dates
        $issuedInvoices = Invoice::query()
            ->whereNotNull('issued_at')
            ->whereBetween('issued_at', [$start, $end])
            ->get();

        foreach ($issuedInvoices as $invoice) {
            $events[] = [
                'id'            => 'issued-' . $invoice->id,
                'title'         => '✅ Issued: ' . ($invoice->invoice_number),
                'start'         => $invoice->issued_at->toDateString(),
                'color'         => '#22c55e',
                'textColor'     => '#fff',
                'url'           => '/accountant/invoices/' . $invoice->id,
                'extendedProps' => ['type' => 'invoice_issued'],
            ];
        }

        // Paid invoices
        $paidInvoices = Invoice::query()
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$start, $end])
            ->get();

        foreach ($paidInvoices as $invoice) {
            $events[] = [
                'id'            => 'paid-' . $invoice->id,
                'title'         => '💰 Paid: ' . ($invoice->invoice_number) . ' — $' . number_format($invoice->total, 0),
                'start'         => $invoice->paid_at->toDateString(),
                'color'         => '#6366f1',
                'textColor'     => '#fff',
                'url'           => '/accountant/invoices/' . $invoice->id,
                'extendedProps' => ['type' => 'invoice_paid'],
            ];
        }

        return $events;
    }

    protected function headerActions(): array
    {
        return [];
    }

    protected function modalActions(): array
    {
        return [];
    }
}
