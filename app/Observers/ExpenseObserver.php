<?php

namespace App\Observers;

use App\Models\Expense;
use App\Models\FinancialApproval;
use App\Notifications\NotificationEvent;
use App\Services\NotificationRouter;

class ExpenseObserver
{
    public function created(Expense $expense): void
    {
        $submitter = $expense->submittedBy?->name ?? 'A staff member';
        $amount    = '$' . number_format($expense->amount, 2);
        $wo        = $expense->workOrder?->reference_number ?? '—';

        app(NotificationRouter::class)->dispatch(new NotificationEvent(
            type:           'expense.submitted',
            title:          'New Expense Submitted for Approval',
            body:           "{$submitter} submitted a {$expense->category} expense of {$amount} on job {$wo}. Please review.",
            icon:           'heroicon-o-receipt-percent',
            color:          'warning',
            actionUrl:      route('filament.admin.resources.expenses.index'),
            actionText:     'Review Expenses',
            recipientRoles: ['super_admin', 'manager'],
            subjectType:    Expense::class,
            subjectId:      $expense->id,
            priority:       'normal',
            idempotencyKey: "expense.submitted.{$expense->id}",
            extraData: [
                'whatsapp_template' => 'expense_submitted',
                'whatsapp_variables' => [$submitter, $expense->category, $amount, $wo],
            ],
        ));
    }

    public function updated(Expense $expense): void
    {
        if (! $expense->isDirty('approval_status')) {
            return;
        }

        $newStatus = $expense->approval_status;

        if ($newStatus === 'approved') {
            $this->notifyRequesterApproved($expense);
        } elseif ($newStatus === 'rejected') {
            $this->notifyRequesterRejected($expense);
        }

        if (in_array($newStatus, ['approved', 'rejected'])) {
            $this->logFinancialApproval($expense, $newStatus);
        }
    }

    private function notifyRequesterApproved(Expense $expense): void
    {
        if (! $expense->submitted_by) {
            return;
        }

        $approverName = $expense->approvedBy?->name ?? 'Admin';
        $amount       = '$' . number_format($expense->amount, 2);

        app(NotificationRouter::class)->dispatch(new NotificationEvent(
            type:             'expense.approved',
            title:            'Your Expense Has Been Approved',
            body:             "Your {$expense->category} expense of {$amount} on {$expense->expense_date?->format('d M Y')} has been approved by {$approverName}.",
            icon:             'heroicon-o-check-circle',
            color:            'success',
            recipientUserIds: [$expense->submitted_by],
            subjectType:      Expense::class,
            subjectId:        $expense->id,
            priority:         'high',
            idempotencyKey:   "expense.approved.{$expense->id}",
            extraData: [
                'whatsapp_template' => 'expense_approved',
                'whatsapp_variables' => [$expense->category, $amount, $expense->expense_date?->format('d M Y') ?? 'Recently', $approverName],
            ],
        ));
    }

    private function notifyRequesterRejected(Expense $expense): void
    {
        if (! $expense->submitted_by) {
            return;
        }

        $amount = '$' . number_format($expense->amount, 2);
        $body   = "Your {$expense->category} expense of {$amount} has been rejected.";
        if ($expense->rejection_reason) {
            $body .= " Reason: \"{$expense->rejection_reason}\"";
        }

        app(NotificationRouter::class)->dispatch(new NotificationEvent(
            type:             'expense.rejected',
            title:            'Your Expense Was Rejected',
            body:             $body,
            icon:             'heroicon-o-x-circle',
            color:            'danger',
            recipientUserIds: [$expense->submitted_by],
            subjectType:      Expense::class,
            subjectId:        $expense->id,
            priority:         'high',
            idempotencyKey:   "expense.rejected.{$expense->id}",
            extraData: [
                'whatsapp_template' => 'expense_rejected',
                'whatsapp_variables' => [$expense->rejection_reason ?? 'No reason provided'],
            ],
        ));
    }

    private function logFinancialApproval(Expense $expense, string $status): void
    {
        FinancialApproval::create([
            'approvable_type' => Expense::class,
            'approvable_id'   => $expense->id,
            'status'          => $status,
            'requested_by'    => $expense->submitted_by,
            'reviewed_by'     => auth()->id(),
            'reviewed_at'     => now(),
            'notes'           => $status === 'rejected' && $expense->rejection_reason
                ? "Rejected: {$expense->rejection_reason}"
                : ucfirst($status),
        ]);
    }
}
