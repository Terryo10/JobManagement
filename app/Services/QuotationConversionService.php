<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

class QuotationConversionService
{
    public function toWorkOrder(Quotation $quotation, User $user, array $options = []): WorkOrder
    {
        return DB::transaction(function () use ($quotation, $user, $options) {
            $quotation = Quotation::query()->lockForUpdate()->with('items')->findOrFail($quotation->id);

            if ($quotation->work_order_id) {
                return WorkOrder::findOrFail($quotation->work_order_id);
            }

            $firstItem = $quotation->items->first();
            $description = $quotation->items->map(function ($item) {
                $quantity = rtrim(rtrim(number_format((float) $item->quantity, 2, '.', ''), '0'), '.');

                return "{$quantity} {$item->unit} — {$item->description}";
            })->implode("\n");

            if ($quotation->notes) {
                $description .= ($description ? "\n\n" : '').$quotation->notes;
            }

            $workOrder = WorkOrder::create([
                'client_id' => $quotation->client_id,
                'title' => $firstItem?->description ?: "Job from {$quotation->quotation_number}",
                'description' => $description ?: "Generated from quotation {$quotation->quotation_number}",
                'category' => $options['category'] ?? 'media',
                'status' => 'pending',
                'priority' => $options['priority'] ?? 'normal',
                'budget' => $quotation->total,
                'assigned_department_id' => $options['assigned_department_id'] ?? null,
                'deadline' => $options['deadline'] ?? null,
                'created_by' => $user->id,
                'details' => [
                    'source_quotation_id' => $quotation->id,
                    'source_quotation_number' => $quotation->quotation_number,
                    'source_currency' => $quotation->currency,
                    'date_order_received' => now()->toDateString(),
                ],
            ]);

            $quotation->update([
                'work_order_id' => $workOrder->id,
                'status' => 'converted',
            ]);
            $quotation->salesOrder()->update(['work_order_id' => $workOrder->id]);

            return $workOrder;
        });
    }

    public function toSalesOrder(Quotation $quotation, User $user): SalesOrder
    {
        return DB::transaction(function () use ($quotation, $user) {
            $quotation = Quotation::query()->lockForUpdate()->with(['items', 'salesOrder'])->findOrFail($quotation->id);

            if ($quotation->salesOrder) {
                return $quotation->salesOrder;
            }

            $order = SalesOrder::create([
                'quotation_id' => $quotation->id,
                'work_order_id' => $quotation->work_order_id,
                'client_id' => $quotation->client_id,
                'status' => 'draft',
                'currency' => $quotation->currency,
                'subtotal' => $quotation->subtotal,
                'tax_rate' => $quotation->tax_rate,
                'tax_amount' => $quotation->tax_amount,
                'total' => $quotation->total,
                'order_date' => now()->toDateString(),
                'notes' => $quotation->notes,
                'created_by' => $user->id,
            ]);

            foreach ($quotation->items as $item) {
                $order->items()->create($item->only([
                    'description', 'quantity', 'unit', 'unit_price', 'total', 'rate_card_id',
                ]));
            }

            $quotation->update(['status' => 'converted']);

            return $order;
        });
    }
}
