<?php

namespace App\Filament\Staff\Widgets;

use App\Models\StaffAvailability;
use App\Models\Task;
use App\Models\WorkOrder;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class StaffCalendarWidget extends FullCalendarWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function config(): array
    {
        return [
            'initialView' => 'timeGridWeek',
            'headerToolbar' => [
                'left'   => 'prev,next today',
                'center' => 'title',
                'right'  => 'dayGridMonth,timeGridWeek,listWeek',
            ],
            'height'     => 600,
            'navLinks'   => true,
            'nowIndicator' => true,
        ];
    }

    /**
     * Return events for the currently visible date range.
     * $info keys: start, end, timezone
     */
    public function fetchEvents(array $info): array
    {
        $userId = auth()->id();
        $events = [];

        // Tasks claimed by this staff member with a deadline in range
        Task::where('claimed_by', $userId)
            ->whereNotNull('deadline')
            ->where('deadline', '>=', $info['start'])
            ->where('deadline', '<=', $info['end'])
            ->with('workOrder:id,reference_number')
            ->get()
            ->each(function (Task $task) use (&$events) {
                $start = $task->start_date?->toDateString() ?? $task->deadline->toDateString();
                $end   = $task->deadline->copy()->addDay()->toDateString();

                $events[] = [
                    'id'              => 'task-' . $task->id,
                    'title'           => $task->title . ($task->workOrder ? ' [' . $task->workOrder->reference_number . ']' : ''),
                    'start'           => $start,
                    'end'             => $end,
                    'allDay'          => true,
                    'backgroundColor' => $this->priorityColor($task->priority),
                    'borderColor'     => $this->priorityColor($task->priority),
                    'textColor'       => '#ffffff',
                    'extendedProps'   => [
                        'type'   => 'task',
                        'status' => $task->status,
                    ],
                ];
            });

        // Work orders claimed by this staff member with a deadline in range
        WorkOrder::where('claimed_by', $userId)
            ->whereNotNull('deadline')
            ->where('deadline', '>=', $info['start'])
            ->where('deadline', '<=', $info['end'])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get()
            ->each(function (WorkOrder $wo) use (&$events) {
                $start = $wo->start_date?->toDateString() ?? $wo->deadline->toDateString();
                $end   = $wo->deadline->copy()->addDay()->toDateString();

                $events[] = [
                    'id'              => 'wo-' . $wo->id,
                    'title'           => '📋 ' . $wo->reference_number . ' — ' . $wo->title,
                    'start'           => $start,
                    'end'             => $end,
                    'allDay'          => true,
                    'backgroundColor' => $this->statusColor($wo->status),
                    'borderColor'     => $this->statusColor($wo->status),
                    'textColor'       => '#ffffff',
                    'extendedProps'   => [
                        'type'   => 'work_order',
                        'status' => $wo->status,
                    ],
                ];
            });

        // Unavailability periods — shown as background blocks
        StaffAvailability::where('user_id', $userId)
            ->where('unavailable_from', '<=', $info['end'])
            ->where('unavailable_to', '>=', $info['start'])
            ->get()
            ->each(function (StaffAvailability $period) use (&$events) {
                $events[] = [
                    'id'      => 'unavail-' . $period->id,
                    'title'   => 'Leave: ' . ucfirst(str_replace('_', ' ', $period->reason ?? 'Unavailable')),
                    'start'   => $period->unavailable_from->toDateString(),
                    'end'     => $period->unavailable_to->copy()->addDay()->toDateString(),
                    'allDay'  => true,
                    'display' => 'background',
                    'backgroundColor' => '#fca5a5', // light red background stripe
                    'extendedProps'   => ['type' => 'unavailability'],
                ];
            });

        return $events;
    }

    public function onEventClick(array $event): void
    {
        // Read-only calendar — no modal or record resolution
    }

    protected function headerActions(): array
    {
        return [];
    }

    protected function modalActions(): array
    {
        return [];
    }

    private function priorityColor(string $priority): string
    {
        return match ($priority) {
            'low'    => '#10b981',
            'medium' => '#3b82f6',
            'high'   => '#f59e0b',
            'urgent' => '#ef4444',
            default  => '#6b7280',
        };
    }

    private function statusColor(string $status): string
    {
        return match ($status) {
            'pending'     => '#94a3b8',
            'in_progress' => '#6366f1',
            'on_hold'     => '#f59e0b',
            'completed'   => '#10b981',
            'cancelled'   => '#ef4444',
            default       => '#6b7280',
        };
    }
}
