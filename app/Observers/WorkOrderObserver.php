<?php

namespace App\Observers;

use App\Models\WorkOrder;
use App\Models\User;
use App\Notifications\DatabaseAlert;

class WorkOrderObserver
{
    public function created(WorkOrder $workOrder): void
    {
        // Notify admins and managers about new job card
        $recipients = User::role(['super_admin', 'manager'])->get();
        foreach ($recipients as $user) {
            $user->notify(new DatabaseAlert(
                title: 'New Job Card Created',
                body: "Job {$workOrder->reference_number}: {$workOrder->title}",
                icon: 'heroicon-o-clipboard-document-list',
                color: 'info',
            ));
        }

        // Notify assigned department head
        if ($workOrder->assigned_department_id) {
            $deptHeads = User::where('department_id', $workOrder->assigned_department_id)
                ->role('dept_head')
                ->get();
            foreach ($deptHeads as $user) {
                $user->notify(new DatabaseAlert(
                    title: 'New Job Assigned to Your Dept',
                    body: "{$workOrder->reference_number}: {$workOrder->title}",
                    icon: 'heroicon-o-clipboard-document-list',
                    color: 'warning',
                ));
            }
        }
    }

    public function updated(WorkOrder $workOrder): void
    {
        // Notify Super Admin when a job is claimed
        if ($workOrder->isDirty('claimed_by') && $workOrder->claimed_by) {
            $claimer = User::find($workOrder->claimed_by);
            $admins = User::role('super_admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new DatabaseAlert(
                    title: 'Job Claimed',
                    body: "{$workOrder->reference_number} was claimed by {$claimer?->name}",
                    icon: 'heroicon-o-hand-raised',
                    color: 'info',
                ));
            }
        }

        // Notify on status change
        if ($workOrder->isDirty('status')) {
            $oldStatus = $workOrder->getOriginal('status');
            $newStatus = $workOrder->status;

            // Notify the creator
            if ($workOrder->created_by) {
                $creator = User::find($workOrder->created_by);
                if ($creator) {
                    $creator->notify(new DatabaseAlert(
                        title: 'Job Card Status Updated',
                        body: "{$workOrder->reference_number} changed from {$oldStatus} to {$newStatus}",
                        icon: 'heroicon-o-arrow-path',
                        color: 'warning',
                    ));
                }
            }

            // Notify department head
            if ($workOrder->assigned_department_id) {
                $deptUsers = User::where('department_id', $workOrder->assigned_department_id)
                    ->role('dept_head')
                    ->get();
                foreach ($deptUsers as $user) {
                    $user->notify(new DatabaseAlert(
                        title: 'Job Status Changed',
                        body: "{$workOrder->reference_number}: {$oldStatus} → {$newStatus}",
                        icon: 'heroicon-o-arrow-path',
                        color: 'warning',
                    ));
                }
            }
        }

        // Budget alert
        if ($workOrder->budget && $workOrder->actual_cost) {
            $percentage = ($workOrder->actual_cost / $workOrder->budget) * 100;
            if ($percentage >= $workOrder->budget_alert_threshold && $workOrder->isDirty('actual_cost')) {
                $managers = User::role(['super_admin', 'manager'])->get();
                foreach ($managers as $manager) {
                    $manager->notify(new DatabaseAlert(
                        title: 'Budget Alert',
                        body: "{$workOrder->reference_number} has reached " . round($percentage) . "% of budget",
                        icon: 'heroicon-o-exclamation-triangle',
                        color: 'danger',
                    ));
                }
            }
        }
    }
}
