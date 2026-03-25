<?php

namespace App\Observers;

use App\Models\AdminTask;
use App\Notifications\NotificationEvent;
use App\Services\NotificationRouter;

class AdminTaskObserver
{
    public function created(AdminTask $task): void
    {
        if (! $task->assigned_to) {
            return;
        }

        app(NotificationRouter::class)->dispatch(new NotificationEvent(
            type:             'admin_task.assigned',
            title:            'Admin Task Assigned',
            body:             "You've been assigned an admin task: {$task->title}",
            icon:             'heroicon-o-clipboard-document-check',
            color:            'primary',
            recipientUserIds: [$task->assigned_to],
            subjectType:      AdminTask::class,
            subjectId:        $task->id,
            priority:         'high',
        ));
    }

    public function updated(AdminTask $task): void
    {
        $router = app(NotificationRouter::class);

        // Assigned to a new person
        if ($task->isDirty('assigned_to') && $task->assigned_to) {
            $isSelf = auth()->check() && auth()->id() === (int) $task->assigned_to;

            if (! $isSelf) {
                $router->dispatch(new NotificationEvent(
                    type:             'admin_task.assigned',
                    title:            'Admin Task Assigned to You',
                    body:             "You've been assigned: {$task->title}",
                    icon:             'heroicon-o-clipboard-document-check',
                    color:            'primary',
                    recipientUserIds: [$task->assigned_to],
                    subjectType:      AdminTask::class,
                    subjectId:        $task->id,
                    priority:         'high',
                ));
            }
        }

        // Status changed
        if ($task->isDirty('status')) {
            $old = $task->getOriginal('status');
            $new = $task->status;

            $notifyIds = array_filter(array_unique([
                $task->created_by,
                $task->assigned_to,
            ]));

            if ($new === 'completed') {
                $router->dispatch(new NotificationEvent(
                    type:             'admin_task.completed',
                    title:            'Admin Task Completed',
                    body:             "\"{$task->title}\" has been marked as completed.",
                    icon:             'heroicon-o-check-badge',
                    color:            'success',
                    recipientUserIds: $notifyIds,
                    recipientRoles:   ['super_admin', 'manager'],
                    subjectType:      AdminTask::class,
                    subjectId:        $task->id,
                ));
            } else {
                $router->dispatch(new NotificationEvent(
                    type:             'admin_task.status_changed',
                    title:            'Admin Task Status Updated',
                    body:             "{$task->title}: {$old} → {$new}",
                    icon:             'heroicon-o-arrow-path',
                    color:            'warning',
                    recipientUserIds: $notifyIds,
                    subjectType:      AdminTask::class,
                    subjectId:        $task->id,
                ));
            }
        }

        // Priority escalated to urgent
        if ($task->isDirty('priority') && $task->priority === 'urgent') {
            $notifyIds = array_filter(array_unique([
                $task->assigned_to,
            ]));

            $router->dispatch(new NotificationEvent(
                type:             'admin_task.urgent',
                title:            'Admin Task Marked Urgent',
                body:             "\"{$task->title}\" has been escalated to urgent priority.",
                icon:             'heroicon-o-exclamation-circle',
                color:            'danger',
                recipientUserIds: $notifyIds,
                recipientRoles:   ['super_admin', 'manager'],
                subjectType:      AdminTask::class,
                subjectId:        $task->id,
                priority:         'high',
            ));
        }
    }
}
