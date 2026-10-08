<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

class QuotationConversionService
{
    public function toWorkOrder(Quotation $quotation, User $user, array $options = []): WorkOrder
    {
        return DB::transaction(function () use ($quotation, $user, $options) {
            $quotation = Quotation::query()->lockForUpdate()->with('items')->findOrFail($quotation->id);
            $linkedWorkOrder = $quotation->work_order_id
                ? WorkOrder::find($quotation->work_order_id)
                : null;
            $workOrder = $linkedWorkOrder
                && $linkedWorkOrder->client_id === $quotation->client_id
                && (int) ($linkedWorkOrder->details['source_quotation_id'] ?? 0) === $quotation->id
                    ? $linkedWorkOrder
                    : $this->createWorkOrder($quotation, $user, $options);

            $quotation->update([
                'work_order_id' => $workOrder->id,
                'status' => 'converted',
            ]);

            // Keep legacy links consistent without creating another sales order.
            $quotation->salesOrder()->update(['work_order_id' => $workOrder->id]);

            if ($linkedWorkOrder && $linkedWorkOrder->id !== $workOrder->id
                && (int) ($linkedWorkOrder->details['source_quotation_id'] ?? 0) === $quotation->id) {
                $details = $linkedWorkOrder->details;
                foreach (array_keys($this->sourceDetails($quotation)) as $key) {
                    unset($details[$key]);
                }
                $linkedWorkOrder->update(['details' => $details ?: null]);
            }

            return $workOrder;
        });
    }

    private function createWorkOrder(Quotation $quotation, User $user, array $options): WorkOrder
    {
        $firstItem = $quotation->items->first();
        $description = $quotation->items->map(function ($item) {
            $quantity = rtrim(rtrim(number_format((float) $item->quantity, 2, '.', ''), '0'), '.');

            return "{$quantity} {$item->unit} — {$item->description}";
        })->implode("\n");

        if ($quotation->notes) {
            $description .= ($description ? "\n\n" : '').$quotation->notes;
        }

        $budget = $quotation->hasIncompleteTotals() ? null : $quotation->total;

        return WorkOrder::create([
            'client_id' => $quotation->client_id,
            'title' => $firstItem?->description ?: "Job from {$quotation->quotation_number}",
            'description' => $description ?: "Generated from quotation {$quotation->quotation_number}",
            'category' => $options['category'] ?? null,
            'status' => 'pending',
            'priority' => $options['priority'] ?? null,
            'budget' => $budget,
            'actual_cost' => null,
            'budget_alert_threshold' => null,
            'assigned_department_id' => $options['assigned_department_id'] ?? null,
            'deadline' => $options['deadline'] ?? null,
            'created_by' => $user->id,
            'details' => $this->sourceDetails($quotation),
        ]);
    }

    private function sourceDetails(Quotation $quotation): array
    {
        $totals = $quotation->hasIncompleteTotals() ? null : $quotation;

        return [
            'source_quotation_id' => $quotation->id,
            'source_quotation_number' => $quotation->quotation_number,
            'source_currency' => $quotation->currency,
            'source_phone' => $quotation->phone,
            'source_valid_until' => $quotation->valid_until?->toDateString(),
            'source_bank_account_id' => $quotation->bank_account_id,
            'source_subtotal' => $totals?->subtotal,
            'source_tax_rate' => $totals?->tax_rate,
            'source_tax_amount' => $totals?->tax_amount,
            'source_total' => $totals?->total,
            'source_notes' => $quotation->notes,
            'source_items' => $quotation->items->map(fn ($item) => $item->only([
                'description', 'quantity', 'unit', 'unit_price', 'total', 'rate_card_id',
            ]))->values()->all(),
        ];
    }
}
