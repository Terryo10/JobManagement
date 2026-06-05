<?php

namespace App\Services;

use App\Models\InventoryRequisition;
use App\Models\Material;
use App\Models\StockLevel;
use App\Models\User;
use App\Notifications\NotificationEvent;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    // ─────────────────────────────────────────────
    // Stock Queries
    // ─────────────────────────────────────────────

    /**
     * Return the current stock quantity for a material (0 if no stock row exists).
     */
    public function currentStock(Material $material): float
    {
        return (float) ($material->stockLevel?->current_quantity ?? 0);
    }

    /**
     * Return true if the material has enough stock for the given quantity.
     */
    public function hasStock(Material $material, float $qty): bool
    {
        return $this->currentStock($material) >= $qty;
    }

    /**
     * Ensure a stock_levels row exists for the material.
     * Returns the StockLevel model (creating it at 0 if absent).
     */
    public function ensureStockLevel(Material $material): StockLevel
    {
        return StockLevel::firstOrCreate(
            ['material_id' => $material->id],
            [
                'current_quantity' => 0,
                'last_updated'     => now(),
                'last_updated_by'  => auth()->id(),
            ]
        );
    }

    // ─────────────────────────────────────────────
    // Inventory Requisition Actions
    // ─────────────────────────────────────────────

    /**
     * Approve a pending inventory requisition (draw-from-stock type).
     * Checks stock and records approval. Does NOT deduct yet — deduction
     * happens when the item is physically issued (see issueFromStock()).
     *
     * @throws \RuntimeException if requisition is not in 'pending' status
     */
    public function approveInventoryRequisition(InventoryRequisition $req, User $approver): void
    {
        if ($req->status !== 'pending') {
            throw new \RuntimeException("Requisition {$req->reference_number} is not pending.");
        }

        DB::transaction(function () use ($req, $approver) {
            $req->update([
                'status'      => 'approved',
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);
        });

        // Notify the requester that their inventory requisition was approved
        if ($req->requested_by) {
            $materialName = $req->material?->name ?? 'item';

            app(NotificationRouter::class)->dispatch(new NotificationEvent(
                type:             'inventory_requisition.approved',
                title:            'Your Inventory Requisition Was Approved',
                body:             "Your request for {$req->quantity_requested} {$req->material?->unit} of \"{$materialName}\" ({$req->reference_number}) has been approved by {$approver->name}. Items will be issued shortly.",
                icon:             'heroicon-o-check-circle',
                color:            'success',
                recipientUserIds: [$req->requested_by],
                subjectType:      InventoryRequisition::class,
                subjectId:        $req->id,
                priority:         'high',
                idempotencyKey:   "inv_req.approved.{$req->id}",
            ));
        }
    }

    /**
     * Issue items from existing stock for an approved inventory requisition.
     * Deducts stock and marks the requisition as issued.
     *
     * @throws \RuntimeException if not in 'approved' status or insufficient stock
     */
    public function issueFromStock(InventoryRequisition $req, User $issuedBy): void
    {
        if ($req->status !== 'approved') {
            throw new \RuntimeException("Requisition {$req->reference_number} must be approved before issuing.");
        }

        DB::transaction(function () use ($req, $issuedBy) {
            $material   = $req->material;
            $stockLevel = $this->ensureStockLevel($material);

            // deduct() throws if insufficient stock — transaction rolls back automatically
            $stockLevel->deduct(
                qty:       (float) $req->quantity_requested,
                by:        $issuedBy,
                reference: $req,
                notes:     "Issued for requisition {$req->reference_number}",
            );

            $req->update([
                'status'           => 'issued',
                'quantity_issued'  => $req->quantity_requested,
                'issued_at'        => now(),
            ]);
        });

        // Notify the requester that their items have been issued
        if ($req->requested_by) {
            $materialName = $req->material?->name ?? 'item';

            app(NotificationRouter::class)->dispatch(new NotificationEvent(
                type:             'inventory_requisition.issued',
                title:            'Your Requested Items Have Been Issued',
                body:             "{$req->quantity_requested} {$req->material?->unit} of \"{$materialName}\" ({$req->reference_number}) has been issued. Please collect from stores.",
                icon:             'heroicon-o-inbox-arrow-down',
                color:            'success',
                recipientUserIds: [$req->requested_by],
                subjectType:      InventoryRequisition::class,
                subjectId:        $req->id,
                priority:         'high',
                idempotencyKey:   "inv_req.issued.{$req->id}",
            ));
        }
    }

    /**
     * Reject a requisition with a reason.
     *
     * @throws \RuntimeException if already issued or rejected
     */
    public function reject(InventoryRequisition $req, User $rejectedBy, string $reason): void
    {
        if (in_array($req->status, ['issued', 'rejected'])) {
            throw new \RuntimeException("Requisition {$req->reference_number} cannot be rejected in its current state.");
        }

        DB::transaction(function () use ($req, $rejectedBy, $reason) {
            $req->update([
                'status'           => 'rejected',
                'approved_by'      => $rejectedBy->id,
                'approved_at'      => now(),
                'rejection_reason' => $reason,
            ]);
        });

        // Notify the requester that their requisition was rejected
        if ($req->requested_by) {
            $materialName = $req->material?->name ?? 'item';
            $body = "Your request for \"{$materialName}\" ({$req->reference_number}) has been rejected.";
            if ($reason) {
                $body .= " Reason: \"{$reason}\"";
            }

            app(NotificationRouter::class)->dispatch(new NotificationEvent(
                type:             'inventory_requisition.rejected',
                title:            'Your Inventory Requisition Was Rejected',
                body:             $body,
                icon:             'heroicon-o-x-circle',
                color:            'danger',
                recipientUserIds: [$req->requested_by],
                subjectType:      InventoryRequisition::class,
                subjectId:        $req->id,
                priority:         'high',
                idempotencyKey:   "inv_req.rejected.{$req->id}",
            ));
        }
    }

    // ─────────────────────────────────────────────
    // Procurement Requisition Actions
    // ─────────────────────────────────────────────

    /**
     * Approve the budget/money for a procurement requisition.
     *
     * @throws \RuntimeException if not in 'pending' status
     */
    public function approveProcurementMoney(InventoryRequisition $req, User $approver): void
    {
        if ($req->status !== 'pending' || $req->type !== 'procurement') {
            throw new \RuntimeException("Requisition {$req->reference_number} is not a pending procurement requisition.");
        }

        DB::transaction(function () use ($req, $approver) {
            $req->update([
                'status'      => 'money_approved',
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);
        });
    }

    /**
     * Mark that funds have been physically issued to the requester.
     *
     * @throws \RuntimeException if not in 'money_approved' status
     */
    public function markMoneyIssued(InventoryRequisition $req, User $issuedBy): void
    {
        if ($req->status !== 'money_approved') {
            throw new \RuntimeException("Budget must be approved before marking funds as issued.");
        }

        DB::transaction(function () use ($req, $issuedBy) {
            $req->update([
                'status'      => 'money_issued',
                'approved_by' => $issuedBy->id, // last actor
            ]);
        });
    }

    /**
     * Requester confirms they have purchased the items (Step B).
     * Transitions to 'items_purchased'.
     *
     * @throws \RuntimeException if not in 'money_issued' status
     */
    public function confirmPurchased(InventoryRequisition $req, User $confirmedBy, ?int $purchaseOrderId = null): void
    {
        if ($req->status !== 'money_issued') {
            throw new \RuntimeException("Funds must be issued before confirming purchase.");
        }

        DB::transaction(function () use ($req, $confirmedBy, $purchaseOrderId) {
            $req->update([
                'status'           => 'items_purchased',
                'purchase_order_id' => $purchaseOrderId,
            ]);
        });
    }

    /**
     * Confirm acquisition: add purchased items to stock (Step C),
     * then automatically issue to the originating inventory requisition.
     *
     * This is the "auto-issue" step described in the requirements:
     *  1. Stock is added to the ledger (items_received)
     *  2. The originating inventory req is deducted from stock and marked issued
     *
     * @param  float         $quantityReceived   May differ from requested (partial receipts)
     * @throws \RuntimeException if not in 'items_purchased' status
     */
    public function confirmReceiptAndAutoIssue(
        InventoryRequisition $procurementReq,
        User                 $receivedBy,
        float                $quantityReceived,
        ?string              $notes = null,
    ): void {
        if ($procurementReq->status !== 'items_purchased') {
            throw new \RuntimeException("Items must be confirmed as purchased before receipt can be logged.");
        }

        DB::transaction(function () use ($procurementReq, $receivedBy, $quantityReceived, $notes) {
            $material   = $procurementReq->material;
            $stockLevel = $this->ensureStockLevel($material);

            // Step C: Add newly purchased items to the inventory ledger
            $stockLevel->add(
                qty:       $quantityReceived,
                by:        $receivedBy,
                reference: $procurementReq,
                notes:     $notes ?? "Stock received from procurement {$procurementReq->reference_number}",
            );

            // Mark the procurement req as items_received
            $procurementReq->update([
                'status'          => 'items_received',
                'quantity_issued' => $quantityReceived,
                'issued_at'       => now(),
            ]);

            // Auto-Issue: find the originating inventory requisition (if any) and issue it
            $originatingReq = $procurementReq->originatingRequisition;

            if ($originatingReq && in_array($originatingReq->status, ['pending', 'approved'])) {
                // If it's pending, first approve it
                if ($originatingReq->status === 'pending') {
                    $originatingReq->update([
                        'status'      => 'approved',
                        'approved_by' => $receivedBy->id,
                        'approved_at' => now(),
                    ]);
                }

                // Reload stock level to reflect the addition above
                $stockLevel->refresh();

                // Then issue (deduct) from the freshly received stock
                $issuableQty = min((float) $originatingReq->quantity_requested, $quantityReceived);

                $stockLevel->deduct(
                    qty:       $issuableQty,
                    by:        $receivedBy,
                    reference: $originatingReq,
                    notes:     "Auto-issued from procurement {$procurementReq->reference_number}",
                );

                $originatingReq->update([
                    'status'          => 'issued',
                    'quantity_issued' => $issuableQty,
                    'issued_at'       => now(),
                ]);
            }

            // Also mark the procurement req as fully issued
            $procurementReq->update(['status' => 'issued']);
        });
    }
}
