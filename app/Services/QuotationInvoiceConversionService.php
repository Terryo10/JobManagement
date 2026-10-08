<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuotationInvoiceConversionService
{
    public function convert(Quotation $quotation, User $user): Invoice
    {
        return DB::transaction(function () use ($quotation, $user) {
            $quotation = Quotation::query()->lockForUpdate()->with(['items', 'workOrder'])
                ->findOrFail($quotation->id);

            if (! in_array($quotation->status, ['sent', 'accepted', 'converted'], true)) {
                throw ValidationException::withMessages([
                    'quotation' => 'Only sent, accepted, or converted quotations can be invoiced.',
                ]);
            }

            $existing = Invoice::withTrashed()->where('quotation_id', $quotation->id)->first();
            if ($existing) {
                if ($existing->trashed()) {
                    throw ValidationException::withMessages([
                        'quotation' => 'This quotation has a deleted invoice. Restore that invoice before converting again.',
                    ]);
                }

                return $existing;
            }

            if ($quotation->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'Add quotation line items before creating an invoice.',
                ]);
            }

            $items = $quotation->items->map(function ($item) {
                if ((float) $item->quantity <= 0 || (float) $item->unit_price < 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Each quotation item needs a positive quantity and a valid unit price.',
                    ]);
                }

                return [
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'unit_price' => $item->unit_price,
                    'total' => round((float) $item->quantity * (float) $item->unit_price, 2),
                    'rate_card_id' => $item->rate_card_id,
                ];
            });

            $subtotal = round($items->sum('total'), 2);
            $taxRate = (float) $quotation->tax_rate;
            $taxAmount = round($subtotal * $taxRate / 100, 2);
            $workOrder = $quotation->workOrder;

            $invoice = Invoice::create([
                'quotation_id' => $quotation->id,
                'client_id' => $quotation->client_id,
                'work_order_id' => $workOrder
                    && $workOrder->client_id === $quotation->client_id
                    && (int) ($workOrder->details['source_quotation_id'] ?? 0) === $quotation->id
                        ? $workOrder->id
                        : null,
                'status' => 'draft',
                'currency' => $quotation->currency,
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total' => $subtotal + $taxAmount,
                'notes' => $quotation->notes,
                'bank_account_id' => $quotation->bank_account_id,
                'created_by' => $user->id,
            ]);

            $invoice->items()->createMany($items->all());
            $quotation->update(['status' => 'converted']);

            return $invoice;
        });
    }
}
