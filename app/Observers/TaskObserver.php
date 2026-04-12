<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\User;
use App\Notifications\NotificationEvent;
use App\Services\NotificationRouter;

class TaskObserver
{
    public function created(Task $task): void
    {
        if (! $task->assigned_to) {
            return;
        }

        app(NotificationRouter::class)->dispatch(new NotificationEvent(
            type:             'task.assigned',
            title:            'New Task Assigned',
            body:             "You've been assigned: {$task->title}",
            icon:             'heroicon-o-check-circle',
            color:            'success',
            recipientUserIds: [$task->assigned_to],
            subjectType:      Task::class,
            subjectId:        $task->id,
            priority:         'high',
            extraData: [
                'whatsapp_template' => 'task_assigned',
                'whatsapp_variables' => [$task->title],
            ],
        ));
    }

    public function updated(Task $task): void
    {
        $router = app(NotificationRouter::class);

        // Task released / unassigned
        if ($task->isDirty('claimed_by') && ! $task->claimed_by && $task->getOriginal('claimed_by')) {
            $oldClaimedByUserId = $task->getOriginal('claimed_by');
            $oldClaimedBy = User::find($oldClaimedByUserId);
            $isSelfRelease = auth()->check() && auth()->id() === (int) $oldClaimedByUserId;

            if ($isSelfRelease) {
                $router->dispatch(new NotificationEvent(
                    type:           'task.released',
                    title:          'Task Released',
                    body:           "Task \"{$task->title}\" was successfully released back to the queue.",
                    icon:           'heroicon-o-arrow-uturn-left',
                    color:          'warning',
                    recipientRoles: ['manager', 'super_admin'],
                    recipientUserIds: [$oldClaimedByUserId],
                    subjectType:    Task::class,
                    subjectId:      $task->id,
                    extraData: [
                        'whatsapp_template' => 'task_released',
                        'whatsapp_variables' => [$task->title],
                    ],
                ));
            } else {
                $reasonText = $task->unassignmentReason ? "\nReason: {$task->unassignmentReason}" : "";
                $router->dispatch(new NotificationEvent(
                    type:             'task.unassigned',
                    title:            'Task Unassigned',
                    body:             "You have been unassigned from: {$task->title}{$reasonText}",
                    icon:             'heroicon-o-user-minus',
                    color:            'danger',
                    recipientUserIds: [$oldClaimedByUserId],
                    subjectType:      Task::class,
                    subjectId:        $task->id,
                    priority:         'high',
                    extraData: [
                        'whatsapp_template' => 'task_unassigned',
                        'whatsapp_variables' => [$task->title, $task->unassignmentReason ?? 'Reassigned'],
                    ],
                ));
            }
        }

        // Task claimed / assigned
        if ($task->isDirty('claimed_by') && $task->claimed_by) {
            $assignee    = User::find($task->claimed_by);
            $isSelfClaim = auth()->check() && auth()->id() === (int) $task->claimed_by;

            $recipientRoles = ['super_admin'];
            $recipientUserIds = [];
            if (!$isSelfClaim) {
                $recipientUserIds[] = $task->claimed_by;
            }

            $router->dispatch(new NotificationEvent(
                type:           $isSelfClaim ? 'task.claimed' : 'task.claimed_assigned',
                title:          $isSelfClaim ? 'Task Claimed' : 'Task Assigned to You',
                body:           $isSelfClaim
                    ? "\"{$task->title}\" was claimed by {$assignee?->name}"
                    : "You have been assigned to: \"{$task->title}\"",
                icon:           $isSelfClaim ? 'heroicon-o-hand-raised' : 'heroicon-o-user-plus',
                color:          'info',
                recipientRoles: $recipientRoles,
                recipientUserIds: $recipientUserIds,
                subjectType:    Task::class,
                subjectId:      $task->id,
                extraData: [
                    'whatsapp_template' => $isSelfClaim ? 'task_claimed' : 'task_assigned',
                    'whatsapp_variables' => $isSelfClaim 
                        ? [$task->title, $assignee?->name ?? 'Staff']
                        : [$task->title],
                ],
            ));
        }

        // Assignee changed — skip if they self-claimed
        $isSelfClaim = auth()->check() && auth()->id() === (int) $task->assigned_to;
        if ($task->isDirty('assigned_to') && $task->assigned_to && ! $isSelfClaim) {
            $router->dispatch(new NotificationEvent(
                type:             'task.assigned',
                title:            'Task Assigned to You',
                body:             $task->title,
                icon:             'heroicon-o-user-plus',
                color:            'info',
                recipientUserIds: [$task->assigned_to],
                subjectType:      Task::class,
                subjectId:        $task->id,
                priority:         'high',
                extraData: [
                    'whatsapp_template' => 'task_assigned',
                    'whatsapp_variables' => [$task->title],
                ],
            ));
        }

        // Status changed
        if ($task->isDirty('status')) {
            $workOrder = $task->workOrder;

            if ($workOrder && $workOrder->created_by) {
                $router->dispatch(new NotificationEvent(
                    type:             'task.status_changed',
                    title:            'Task Status Updated',
                    body:             "{$task->title}: {$task->getOriginal('status')} → {$task->status}",
                    icon:             'heroicon-o-arrow-path',
                    color:            'warning',
                    recipientUserIds: [$workOrder->created_by],
                    subjectType:      Task::class,
                    subjectId:        $task->id,
                    extraData: [
                        'whatsapp_template' => 'task_status_update',
                        'whatsapp_variables' => [$task->title, $task->getOriginal('status'), $task->status],
                    ],
                ));
            }

            // All tasks on the work order completed
            if ($task->status === 'completed' && $workOrder) {
                $pendingCount = $workOrder->tasks()
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->where('id', '!=', $task->id)
                    ->count();

                if ($pendingCount === 0) {
                    $router->dispatch(new NotificationEvent(
                        type:           'task.all_completed',
                        title:          'All Tasks Completed',
                        body:           "All tasks for {$workOrder->reference_number} are done",
                        icon:           'heroicon-o-check-badge',
                        color:          'success',
                        recipientRoles: ['super_admin', 'manager'],
                        subjectType:    Task::class,
                        subjectId:      $task->id,
                        priority:       'high',
                        extraData: [
                            'whatsapp_template' => 'task_all_completed',
                            'whatsapp_variables' => [$workOrder->reference_number],
                        ],
                    ));
                }
            }
        }
    }
}
