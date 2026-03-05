<?php

namespace App\Filament\Accountant\Widgets;

use App\Models\Invoice;
use App\Models\WorkOrder;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class AccountantCalendarWidget extends FullCalendarWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function config(): array
    {
        return [
            'initialView' => 'dayGridMonth',
            'headerToolbar' => [
                'left'   => 'prev,next today',
                'center' => 'title',
                'right'  => 'dayGridMonth,listMonth',
            ],
            'height'   => 640,
            'navLinks' => true,
        ];
    }

    public function fetchEvents(array $info): array
    {
        $events = [];

        // --- Invoices by due date ---
        Invoice::whereNotNull('due_at')
            ->where('due_at', '>=', $info['start'])
            ->where('due_at', '<=', $info['end'])
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->with('client:id,company_name')
            ->get()
            ->each(function (Invoice $invoice) use (&$events) {
                $color = $this->invoiceColor($invoice->status, $invoice->due_at);

                $events[] = [
                    'id'              => 'invoice-' . $invoice->id,
                    'title'           => '💰 ' . $invoice->invoice_number . ' — ' . ($invoice->client?->company_name ?? 'Client') . ' ($' . number_format($invoice->total, 2) . ')',
                    'start'           => $invoice->due_at->toDateString(),
                    'end'             => $invoice->due_at->copy()->addDay()->toDateString(),
                    'allDay'          => true,
                    'backgroundColor' => $color,
                    'borderColor'     => $color,
                    'textColor'       => '#ffffff',
                    'url'             => $this->invoiceUrl($invoice->id),
                    'extendedProps'   => [
                        'type'   => 'invoice',
                        'status' => $invoice->status,
                        'total'  => $invoice->total,
                    ],
                ];
            });

        // --- Work order deadlines ---
        WorkOrder::whereNotNull('deadline')
            ->where('deadline', '>=', $info['start'])
            ->where('deadline', '<=', $info['end'])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get()
            ->each(function (WorkOrder $wo) use (&$events) {
                $events[] = [
                    'id'              => 'wo-' . $wo->id,
                    'title'           => '📋 ' . $wo->reference_number . ' — ' . $wo->title,
                    'start'           => $wo->deadline->toDateString(),
                    'end'             => $wo->deadline->copy()->addDay()->toDateString(),
                    'allDay'          => true,
                    'backgroundColor' => '#6366f1',
                    'borderColor'     => '#6366f1',
                    'textColor'       => '#ffffff',
                    'url'             => $this->workOrderUrl($wo->id),
                    'extendedProps'   => [
                        'type'   => 'work_order',
                        'status' => $wo->status,
                    ],
                ];
            });

        return $events;
    }

    private function invoiceColor(string $status, \Carbon\Carbon $dueAt): string
    {
        if ($dueAt->isPast() && $status !== 'paid') {
            return '#ef4444'; // overdue — red
        }

        return match ($status) {
            'draft'  => '#94a3b8', // gray
            'sent'   => '#3b82f6', // blue
            'paid'   => '#10b981', // green
            default  => '#f59e0b', // amber
        };
    }

    private function invoiceUrl(int $id): ?string
    {
        try {
            return route('filament.accountant.resources.invoices.edit', $id);
        } catch (\Exception) {
            return null;
        }
    }

    private function workOrderUrl(int $id): ?string
    {
        try {
            return route('filament.accountant.resources.work-orders.edit', $id);
        } catch (\Exception) {
            return null;
        }
    }

    public function onEventClick(array $event): void
    {
        // Navigation is handled by the `url` key on each event
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
