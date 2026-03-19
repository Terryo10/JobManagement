<?php

namespace App\Filament\Admin\Widgets;

use App\Models\StaffAvailability;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkOrder;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class AdminCalendarWidget extends FullCalendarWidget
{
    protected static ?int $sort = 20;

    protected int|string|array $columnSpan = 'full';

    /**
     * Optional filter: 0 = all staff, otherwise filter by user ID.
     */
    public int $filterUserId = 0;

    public function config(): array
    {
        return [
            'initialView' => 'dayGridMonth',
            'headerToolbar' => [
                'left'   => 'prev,next today',
                'center' => 'title',
                'right'  => 'dayGridMonth,timeGridWeek,listWeek',
            ],
            'height'   => 680,
            'navLinks' => true,
        ];
    }

    public function fetchEvents(array $info): array
    {
        $events = [];

        // --- Tasks ---
        $taskQuery = Task::whereNotNull('deadline')
            ->where('deadline', '>=', $info['start'])
            ->where('deadline', '<=', $info['end'])
            ->with('claimedBy:id,name', 'workOrder:id,reference_number');

        if ($this->filterUserId > 0) {
            $taskQuery->where('claimed_by', $this->filterUserId);
        }

        $taskQuery->get()->each(function (Task $task) use (&$events) {
            $color = $task->claimed_by
                ? $this->userColor($task->claimed_by)
                : '#94a3b8';

            $assigneeName = $task->claimedBy?->name ?? 'Unassigned';
            $start = $task->start_date?->toDateString() ?? $task->deadline->toDateString();
            $end   = $task->deadline->copy()->addDay()->toDateString();

            $events[] = [
                'id'              => 'task-' . $task->id,
                'title'           => $task->title . ' (' . $assigneeName . ')',
                'start'           => $start,
                'end'             => $end,
                'allDay'          => true,
                'backgroundColor' => $color,
                'borderColor'     => $color,
                'textColor'       => '#ffffff',
                'url'             => $this->adminTaskUrl($task->id),
                'extendedProps'   => [
                    'type'     => 'task',
                    'assignee' => $assigneeName,
                    'status'   => $task->status,
                    'priority' => $task->priority,
                ],
            ];
        });

        // --- Work Orders ---
        $woQuery = WorkOrder::whereNotNull('deadline')
            ->where('deadline', '>=', $info['start'])
            ->where('deadline', '<=', $info['end'])
            ->with('claimedBy:id,name');

        if ($this->filterUserId > 0) {
            $woQuery->where('claimed_by', $this->filterUserId);
        }

        $woQuery->get()->each(function (WorkOrder $wo) use (&$events) {
            $color = $wo->claimed_by
                ? $this->userColor($wo->claimed_by)
                : '#475569';

            $assigneeName = $wo->claimedBy?->name ?? 'Unassigned';
            $start = $wo->start_date?->toDateString() ?? $wo->deadline->toDateString();
            $end   = $wo->deadline->copy()->addDay()->toDateString();

            $events[] = [
                'id'              => 'wo-' . $wo->id,
                'title'           => $wo->reference_number . ': ' . $wo->title . ' (' . $assigneeName . ')',
                'start'           => $start,
                'end'             => $end,
                'allDay'          => true,
                'backgroundColor' => $color,
                'borderColor'     => $color,
                'textColor'       => '#ffffff',
                'url'             => $this->adminWorkOrderUrl($wo->id),
                'extendedProps'   => [
                    'type'     => 'work_order',
                    'assignee' => $assigneeName,
                    'status'   => $wo->status,
                ],
            ];
        });

        // --- Staff unavailability (background blocks, filterable by user) ---
        $availQuery = StaffAvailability::with('user:id,name')
            ->where('unavailable_from', '<=', $info['end'])
            ->where('unavailable_to', '>=', $info['start']);

        if ($this->filterUserId > 0) {
            $availQuery->where('user_id', $this->filterUserId);
        }

        $availQuery->get()->each(function (StaffAvailability $period) use (&$events) {
            $events[] = [
                'id'      => 'unavail-' . $period->id,
                'title'   => ($period->user?->name ?? 'Staff') . ' — ' . ucfirst(str_replace('_', ' ', $period->reason ?? 'Leave')),
                'start'   => $period->unavailable_from->toDateString(),
                'end'     => $period->unavailable_to->copy()->addDay()->toDateString(),
                'allDay'  => true,
                'display' => 'background',
                'backgroundColor' => '#fca5a5',
                'extendedProps'   => ['type' => 'unavailability'],
            ];
        });

        // --- Requisitions (Purchase Orders) ---
        \App\Models\PurchaseOrder::whereIn('status', ['finance_approved', 'approved'])
            ->where('created_at', '>=', $info['start'])
            ->where('created_at', '<=', $info['end'])
            ->with(['orderedBy'])
            ->get()
            ->each(function ($po) use (&$events) {
                $statusLabel = $po->status === 'finance_approved' ? ' (Pending Final Approval)' : ' (Approved)';
                $color = $po->status === 'finance_approved' ? '#f59e0b' : '#22c55e'; // Orange vs Green
                
                $events[] = [
                    'id'              => 'po-' . $po->id,
                    'title'           => '📝 REQ: ' . $po->title . $statusLabel,
                    'start'           => $po->created_at->toDateString(),
                    'allDay'          => true,
                    'backgroundColor' => $color,
                    'borderColor'     => $color,
                    'textColor'       => '#ffffff',
                    'url'             => '/admin/purchase-orders/' . $po->id . '/edit',
                    'extendedProps'   => [
                        'type'   => 'requisition',
                        'status' => $po->status,
                    ],
                ];
            });

        return $events;
    }

    /**
     * Deterministic colour from user ID — 8 distinct palette entries.
     */
    private function userColor(int $userId): string
    {
        $palette = [
            '#3b82f6', // blue
            '#10b981', // green
            '#f59e0b', // amber
            '#8b5cf6', // violet
            '#ec4899', // pink
            '#06b6d4', // cyan
            '#84cc16', // lime
            '#f97316', // orange
        ];

        return $palette[$userId % count($palette)];
    }

    private function adminTaskUrl(int $id): ?string
    {
        try {
            return route('filament.admin.resources.tasks.edit', $id);
        } catch (\Exception) {
            return null;
        }
    }

    private function adminWorkOrderUrl(int $id): ?string
    {
        try {
            return route('filament.admin.resources.work-orders.edit', $id);
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

    /**
     * Provide staff list for the user filter in the widget view.
     */
    public function getStaffOptions(): array
    {
        return User::whereHas('roles', fn ($q) => $q->whereIn('name', ['staff', 'dept_head', 'manager', 'super_admin']))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }
}
