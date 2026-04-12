<?php

namespace App\Observers;

use App\Models\FinancialApproval;
use App\Models\PurchaseOrder;
use App\Notifications\NotificationEvent;
use App\Services\NotificationRouter;

class PurchaseOrderObserver
{
    public function updated(PurchaseOrder $order): void
    {
        if (! $order->isDirty('status')) {
            return;
        }

        $oldStatus = $order->getOriginal('status');
        $newStatus = $order->status;

        match (true) {
            // Staff submits → notify Finance/Accountant + Admin
            $oldStatus === 'draft' && $newStatus === 'pending_finance_approval'
                => $this->notifyFinanceOnSubmit($order),

            // Finance approves → notify Admin for final sign-off
            $oldStatus === 'pending_finance_approval' && $newStatus === 'finance_approved'
                => $this->notifyAdminOnFinanceApproval($order),

            // Admin gives final approval → notify requester
            $newStatus === 'approved'
                => $this->notifyRequesterApproved($order),

            // Rejected at any stage → notify requester
            $newStatus === 'rejected'
                => $this->notifyRequesterRejected($order),

            default => null,
        };

        // Write FinancialApproval audit record for every finance-relevant transition
        if (in_array($newStatus, ['finance_approved', 'approved', 'rejected'])) {
            $this->logFinancialApproval($order, $newStatus);
        }
    }

    // -------------------------------------------------------------------------

    private function notifyFinanceOnSubmit(PurchaseOrder $order): void
    {
        $requester = $order->orderedBy?->name ?? 'A staff member';
        $amount    = '$' . number_format($order->total_amount, 2);

        app(NotificationRouter::class)->dispatch(new NotificationEvent(
            type:           'requisition.submitted',
            title:          'New Requisition Needs Finance Approval',
            body:           "{$requester} submitted {$order->reference_number} for {$amount} — \"{$order->title}\". Please review.",
            icon:           'heroicon-o-document-currency-dollar',
            color:          'warning',
            actionUrl:      route('filament.accountant.resources.purchase-orders.index'),
            actionText:     'Review Requisitions',
            recipientRoles: ['accountant', 'super_admin'],
            subjectType:    PurchaseOrder::class,
            subjectId:      $order->id,
            priority:       'high',
            idempotencyKey: "req.submitted.{$order->id}",
            extraData: [
                'whatsapp_template' => 'requisition_submitted',
                'whatsapp_variables' => [$requester, $order->reference_number, $amount, $order->title],
            ],
        ));
    }

    private function notifyAdminOnFinanceApproval(PurchaseOrder $order): void
    {
        $financeName = $order->financeApprovedBy?->name ?? 'Finance';
        $amount      = '$' . number_format($order->total_amount, 2);

        app(NotificationRouter::class)->dispatch(new NotificationEvent(
            type:           'requisition.finance_approved',
            title:          'Requisition Awaiting Your Final Approval',
            body:           "{$order->reference_number} ({$amount}) was approved by {$financeName} and needs your final sign-off.",
            icon:           'heroicon-o-check-badge',
            color:          'info',
            actionUrl:      route('filament.admin.resources.purchase-orders.index'),
            actionText:     'Review & Approve',
            recipientRoles: ['super_admin'],
            subjectType:    PurchaseOrder::class,
            subjectId:      $order->id,
            priority:       'high',
            idempotencyKey: "req.finance_approved.{$order->id}",
            extraData: [
                'whatsapp_template' => 'requisition_finance_appr',
                'whatsapp_variables' => [$order->reference_number, $amount, $financeName],
            ],
        ));
    }

    private function notifyRequesterApproved(PurchaseOrder $order): void
    {
        if (! $order->ordered_by) {
            return;
        }

        $approverName = $order->approvedBy?->name ?? 'Admin';
        $amount       = '$' . number_format($order->total_amount, 2);

        app(NotificationRouter::class)->dispatch(new NotificationEvent(
            type:             'requisition.approved',
            title:            'Your Requisition Has Been Approved',
            body:             "{$order->reference_number} ({$amount}) — \"{$order->title}\" has been fully approved by {$approverName}.",
            icon:             'heroicon-o-check-circle',
            color:            'success',
            actionUrl:        route('filament.staff.resources.purchase-orders.index'),
            actionText:       'View Requisitions',
            recipientUserIds: [$order->ordered_by],
            subjectType:      PurchaseOrder::class,
            subjectId:        $order->id,
            priority:         'high',
            idempotencyKey:   "req.approved.{$order->id}",
            extraData: [
                'whatsapp_template' => 'requisition_approved',
                'whatsapp_variables' => [$order->reference_number, $amount, $order->title, $approverName],
            ],
        ));
    }

    private function notifyRequesterRejected(PurchaseOrder $order): void
    {
        if (! $order->ordered_by) {
            return;
        }

        $amount = '$' . number_format($order->total_amount, 2);

        app(NotificationRouter::class)->dispatch(new NotificationEvent(
            type:             'requisition.rejected',
            title:            'Your Requisition Was Rejected',
            body:             "{$order->reference_number} ({$amount}) — \"{$order->title}\" has been rejected. Please contact Finance or Admin for details.",
            icon:             'heroicon-o-x-circle',
            color:            'danger',
            actionUrl:        route('filament.staff.resources.purchase-orders.index'),
            actionText:       'View Requisitions',
            recipientUserIds: [$order->ordered_by],
            subjectType:      PurchaseOrder::class,
            subjectId:        $order->id,
            priority:         'high',
            idempotencyKey:   "req.rejected.{$order->id}",
            extraData: [
                'whatsapp_template' => 'requisition_rejected',
                'whatsapp_variables' => [$order->reference_number, $amount, $order->title],
            ],
        ));
    }

    private function logFinancialApproval(PurchaseOrder $order, string $status): void
    {
        FinancialApproval::create([
            'approvable_type' => PurchaseOrder::class,
            'approvable_id'   => $order->id,
            'status'          => $status,
            'requested_by'    => $order->ordered_by,
            'reviewed_by'     => auth()->id(),
            'reviewed_at'     => now(),
            'notes'           => match ($status) {
                'finance_approved' => "Finance approved by {$order->financeApprovedBy?->name}",
                'approved'         => "Final approval by {$order->approvedBy?->name}",
                'rejected'         => 'Rejected',
                default            => $status,
            },
        ]);
    }
}
