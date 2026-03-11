<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\User;
use App\Notifications\DatabaseAlert;

class TaskObserver
{
    public function created(Task $task): void
    {
        // Notify assigned user
        if ($task->assigned_to) {
            $assignee = User::find($task->assigned_to);
            if ($assignee) {
                $assignee->notify(new DatabaseAlert(
                    title: 'New Task Assigned',
                    body: "You've been assigned: {$task->title}",
                    icon: 'heroicon-o-check-circle',
                    color: 'success',
                ));
            }
        }
    }

    public function updated(Task $task): void
    {
        // Notify Super Admin when a task is claimed
        if ($task->isDirty('claimed_by') && $task->claimed_by) {
            $claimer = User::find($task->claimed_by);
            $admins = User::role('super_admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new DatabaseAlert(
                    title: 'Task Claimed',
                    body: "\"{$task->title}\" was claimed by {$claimer?->name}",
                    icon: 'heroicon-o-hand-raised',
                    color: 'info',
                ));
            }
        }

        // Notify on assignment change
        if ($task->isDirty('assigned_to') && $task->assigned_to) {
            $assignee = User::find($task->assigned_to);
            if ($assignee) {
                $assignee->notify(new DatabaseAlert(
                    title: 'Task Assigned to You',
                    body: $task->title,
                    icon: 'heroicon-o-user-plus',
                    color: 'info',
                ));
            }
        }

        // Notify work order creator on task status change
        if ($task->isDirty('status')) {
            $workOrder = $task->workOrder;
            if ($workOrder && $workOrder->created_by) {
                $creator = User::find($workOrder->created_by);
                if ($creator) {
                    $creator->notify(new DatabaseAlert(
                        title: 'Task Status Updated',
                        body: "{$task->title}: {$task->getOriginal('status')} → {$task->status}",
                        icon: 'heroicon-o-arrow-path',
                        color: 'warning',
                    ));
                }
            }

            // If task completed, check if all tasks for the work order are done
            if ($task->status === 'completed' && $workOrder) {
                $pendingTasks = $workOrder->tasks()
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->where('id', '!=', $task->id)
                    ->count();

                if ($pendingTasks === 0) {
                    $managers = User::role(['super_admin', 'manager'])->get();
                    foreach ($managers as $manager) {
                        $manager->notify(new DatabaseAlert(
                            title: 'All Tasks Completed',
                            body: "All tasks for {$workOrder->reference_number} are done",
                            icon: 'heroicon-o-check-badge',
                            color: 'success',
                        ));
                    }
                }
            }
        }
    }
}
