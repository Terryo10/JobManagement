<?php

namespace App\Observers;

use App\Models\PurchaseOrder;
use App\Notifications\NotificationEvent;
use App\Services\NotificationRouter;

class PurchaseOrderObserver
{
    public function created(PurchaseOrder $order): void
    {
        $router = app(NotificationRouter::class);
        
        if ($order->status === 'pending_finance_approval') {
            $router->dispatch(new NotificationEvent(
                type:           'expense.submitted',
                title:          'New Requisition Submitted',
                body:           "{$order->reference_number} for $" . number_format($order->total_amount, 2),
                icon:           'heroicon-o-document-text',
                color:          'info',
                recipientRoles: ['manager', 'super_admin', 'accountant'],
                subjectType:    PurchaseOrder::class,
                subjectId:      $order->id,
            ));
        }
    }

    public function updated(PurchaseOrder $order): void
    {
        $router = app(NotificationRouter::class);

        if ($order->isDirty('status')) {
            // When it leaves draft and gets submitted
            if ($order->getOriginal('status') === 'draft' && $order->status === 'pending_finance_approval') {
                $router->dispatch(new NotificationEvent(
                    type:           'expense.submitted',
                    title:          'Requisition Submitted',
                    body:           "{$order->reference_number} for $" . number_format($order->total_amount, 2),
                    icon:           'heroicon-o-document-text',
                    color:          'info',
                    recipientRoles: ['manager', 'super_admin', 'accountant'],
                    subjectType:    PurchaseOrder::class,
                    subjectId:      $order->id,
                ));
            }
            
            // Further approvals
            if ($order->status === 'finance_approved') {
                $router->dispatch(new NotificationEvent(
                    type:           'expense.submitted',
                    title:          'Requisition Pending Final Approval',
                    body:           "{$order->reference_number} has been finance approved.",
                    icon:           'heroicon-o-document-text',
                    color:          'warning',
                    recipientRoles: ['super_admin'],
                    subjectType:    PurchaseOrder::class,
                    subjectId:      $order->id,
                ));
            } elseif ($order->status === 'approved') {
                $router->dispatch(new NotificationEvent(
                    type:             'expense.approved',
                    title:            'Requisition Approved',
                    body:             "{$order->reference_number} has been approved.",
                    icon:             'heroicon-o-check-circle',
                    color:            'success',
                    recipientUserIds: [$order->ordered_by],
                    subjectType:      PurchaseOrder::class,
                    subjectId:        $order->id,
                ));
            } elseif ($order->status === 'rejected') {
                $router->dispatch(new NotificationEvent(
                    type:             'expense.rejected',
                    title:            'Requisition Rejected',
                    body:             "{$order->reference_number} has been rejected.",
                    icon:             'heroicon-o-x-circle',
                    color:            'danger',
                    recipientUserIds: [$order->ordered_by],
                    subjectType:      PurchaseOrder::class,
                    subjectId:        $order->id,
                ));
            }
        }
    }
}
