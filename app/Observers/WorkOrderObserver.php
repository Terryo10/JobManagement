<?php

namespace App\Observers;

use App\Models\User;
use App\Models\WorkOrder;
use App\Notifications\NotificationEvent;
use App\Services\NotificationRouter;

class WorkOrderObserver
{
    public function created(WorkOrder $workOrder): void
    {
        $router = app(NotificationRouter::class);

        // Notify admins/managers
        $router->dispatch(new NotificationEvent(
            type:           'work_order.created',
            title:          'New Job Card Created',
            body:           "Job {$workOrder->reference_number}: {$workOrder->title}",
            icon:           'heroicon-o-clipboard-document-list',
            color:          'info',
            recipientRoles: ['super_admin', 'manager'],
            subjectType:    WorkOrder::class,
            subjectId:      $workOrder->id,
        ));

        // Notify assigned department head
        if ($workOrder->assigned_department_id) {
            $deptHeads = User::where('department_id', $workOrder->assigned_department_id)
                ->role('dept_head')
                ->pluck('id')
                ->toArray();

            if ($deptHeads) {
                $router->dispatch(new NotificationEvent(
                    type:             'work_order.assigned_to_department',
                    title:            'New Job Assigned to Your Dept',
                    body:             "{$workOrder->reference_number}: {$workOrder->title}",
                    icon:             'heroicon-o-clipboard-document-list',
                    color:            'warning',
                    recipientUserIds: $deptHeads,
                    subjectType:      WorkOrder::class,
                    subjectId:        $workOrder->id,
                    priority:         'high',
                ));
            }
        }
    }

    public function updated(WorkOrder $workOrder): void
    {
        $router = app(NotificationRouter::class);

        // Job released
        if ($workOrder->isDirty('claimed_by') && ! $workOrder->claimed_by && $workOrder->getOriginal('claimed_by')) {
            $releasedBy = User::find($workOrder->getOriginal('claimed_by'));

            $router->dispatch(new NotificationEvent(
                type:           'work_order.released',
                title:          'Job Released',
                body:           "{$workOrder->reference_number} was released by {$releasedBy?->name}",
                icon:           'heroicon-o-arrow-uturn-left',
                color:          'warning',
                recipientRoles: ['manager', 'super_admin'],
                subjectType:    WorkOrder::class,
                subjectId:      $workOrder->id,
            ));
        }

        // Job claimed / assigned
        if ($workOrder->isDirty('claimed_by') && $workOrder->claimed_by) {
            $assignee    = User::find($workOrder->claimed_by);
            $isSelfClaim = auth()->check() && auth()->id() === (int) $workOrder->claimed_by;

            $router->dispatch(new NotificationEvent(
                type:           $isSelfClaim ? 'work_order.claimed' : 'work_order.assigned',
                title:          $isSelfClaim ? 'Job Claimed' : 'Job Assigned',
                body:           $isSelfClaim
                    ? "{$workOrder->reference_number} was claimed by {$assignee?->name}"
                    : "{$workOrder->reference_number} assigned to {$assignee?->name}",
                icon:           $isSelfClaim ? 'heroicon-o-hand-raised' : 'heroicon-o-user-plus',
                color:          'info',
                recipientRoles: ['super_admin'],
                subjectType:    WorkOrder::class,
                subjectId:      $workOrder->id,
            ));
        }

        // Status changed
        if ($workOrder->isDirty('status')) {
            $oldStatus = $workOrder->getOriginal('status');
            $newStatus = $workOrder->status;

            $recipientIds = [];

            if ($workOrder->created_by) {
                $recipientIds[] = $workOrder->created_by;
            }

            if ($workOrder->assigned_department_id) {
                $deptHeadIds = User::where('department_id', $workOrder->assigned_department_id)
                    ->role('dept_head')
                    ->pluck('id')
                    ->toArray();
                $recipientIds = array_merge($recipientIds, $deptHeadIds);
            }

            if ($recipientIds) {
                $router->dispatch(new NotificationEvent(
                    type:             'work_order.status_changed',
                    title:            'Job Card Status Updated',
                    body:             "{$workOrder->reference_number}: {$oldStatus} → {$newStatus}",
                    icon:             'heroicon-o-arrow-path',
                    color:            'warning',
                    recipientUserIds: array_unique($recipientIds),
                    subjectType:      WorkOrder::class,
                    subjectId:        $workOrder->id,
                ));
            }
        }

        // Budget alert
        if ($workOrder->budget && $workOrder->actual_cost && $workOrder->isDirty('actual_cost')) {
            $percentage = ($workOrder->actual_cost / $workOrder->budget) * 100;

            if ($percentage >= ($workOrder->budget_alert_threshold ?? 80)) {
                $router->dispatch(new NotificationEvent(
                    type:           'work_order.budget_alert',
                    title:          'Budget Alert',
                    body:           "{$workOrder->reference_number} has reached " . round($percentage) . "% of budget",
                    icon:           'heroicon-o-exclamation-triangle',
                    color:          'danger',
                    recipientRoles: ['super_admin', 'manager'],
                    subjectType:    WorkOrder::class,
                    subjectId:      $workOrder->id,
                    priority:       'high',
                ));
            }
        }
    }
}
