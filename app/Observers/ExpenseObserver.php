<?php

namespace App\Observers;

use App\Models\Expense;
use App\Notifications\NotificationEvent;
use App\Services\NotificationRouter;

class ExpenseObserver
{
    public function created(Expense $expense): void
    {
        $submittedBy  = $expense->submittedBy?->name ?? 'Someone';
        $workOrderRef = $expense->workOrder?->reference_number ?? 'N/A';
        $amount       = number_format((float) $expense->amount, 2);

        app(NotificationRouter::class)->dispatch(new NotificationEvent(
            type:           'expense.submitted',
            title:          'New Expense Submitted',
            body:           "{$submittedBy} submitted a {$expense->category} expense of {$expense->currency} {$amount} for {$workOrderRef}",
            icon:           'heroicon-o-banknotes',
            color:          'warning',
            recipientRoles: ['manager', 'super_admin'],
            subjectType:    Expense::class,
            subjectId:      $expense->id,
        ));
    }

    public function updated(Expense $expense): void
    {
        if (! $expense->isDirty('approval_status')) {
            return;
        }

        if (! $expense->submitted_by) {
            return;
        }

        $workOrderRef = $expense->workOrder?->reference_number ?? 'N/A';
        $amount       = number_format((float) $expense->amount, 2);
        $approvedBy   = $expense->approvedBy?->name ?? 'Management';
        $isApproved   = $expense->approval_status === 'approved';

        app(NotificationRouter::class)->dispatch(new NotificationEvent(
            type:             $isApproved ? 'expense.approved' : 'expense.rejected',
            title:            $isApproved ? 'Expense Approved' : 'Expense Rejected',
            body:             $isApproved
                ? "Your {$expense->category} expense of {$expense->currency} {$amount} for {$workOrderRef} has been approved by {$approvedBy}."
                : "Your {$expense->category} expense of {$expense->currency} {$amount} for {$workOrderRef} has been rejected by {$approvedBy}." .
                  ($expense->rejection_reason ? " Reason: {$expense->rejection_reason}" : ''),
            icon:             $isApproved ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle',
            color:            $isApproved ? 'success' : 'danger',
            recipientUserIds: [$expense->submitted_by],
            subjectType:      Expense::class,
            subjectId:        $expense->id,
            priority:         'high',
        ));
    }
}
