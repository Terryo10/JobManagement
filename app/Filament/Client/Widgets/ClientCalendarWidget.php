<?php

namespace App\Filament\Client\Widgets;

use App\Models\WorkOrder;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class ClientCalendarWidget extends FullCalendarWidget
{
    protected static ?int $sort = 4;

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
            'height'   => 600,
            'navLinks' => true,
            'editable' => false,
        ];
    }

    /**
     * Show the client's own work orders as timeline bars.
     * Matched by the logged-in user's email against the clients table.
     */
    public function fetchEvents(array $info): array
    {
        $email  = auth()->user()?->email;
        $events = [];

        WorkOrder::whereHas('client', fn ($q) => $q->where('email', $email))
            ->where(function ($q) use ($info) {
                // Include WOs whose deadline falls in range, or whose span overlaps range
                $q->where('deadline', '>=', $info['start'])
                  ->where(function ($inner) use ($info) {
                      $inner->whereNull('start_date')
                            ->orWhere('start_date', '<=', $info['end']);
                  });
            })
            ->whereNotIn('status', ['cancelled'])
            ->with('client:id,company_name')
            ->get()
            ->each(function (WorkOrder $wo) use (&$events) {
                $color = $this->statusColor($wo->status);
                $start = $wo->start_date?->toDateString() ?? $wo->deadline->toDateString();
                $end   = $wo->deadline->copy()->addDay()->toDateString();

                $events[] = [
                    'id'              => 'wo-' . $wo->id,
                    'title'           => $wo->reference_number . ': ' . $wo->title,
                    'start'           => $start,
                    'end'             => $end,
                    'allDay'          => true,
                    'backgroundColor' => $color,
                    'borderColor'     => $color,
                    'textColor'       => '#ffffff',
                    'extendedProps'   => [
                        'type'     => 'work_order',
                        'status'   => $wo->status,
                        'priority' => $wo->priority,
                    ],
                ];
            });

        return $events;
    }

    private function statusColor(string $status): string
    {
        return match ($status) {
            'pending'     => '#94a3b8', // gray
            'in_progress' => '#3b82f6', // blue
            'on_hold'     => '#f59e0b', // amber
            'completed'   => '#10b981', // green
            'cancelled'   => '#6b7280', // dark gray
            default       => '#6b7280',
        };
    }

    public function onEventClick(array $event): void
    {
        // Read-only calendar — no interaction
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
