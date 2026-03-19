<?php

namespace App\Filament\Staff\Resources\WorkOrderResource\Pages;

use App\Filament\Staff\Resources\WorkOrderResource;
use App\Models\WorkOrder;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkOrder extends CreateRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['claimed_by'] = auth()->id();
        $data['claimed_at'] = now();
        $data['created_by'] = auth()->id();

        // Auto-generate reference number
        $prefix = 'WO-' . date('Y') . '-';
        $lastOrder = WorkOrder::where('reference_number', 'like', $prefix . '%')
            ->orderByDesc('reference_number')
            ->first();
        $nextNum = $lastOrder
            ? (int) str_replace($prefix, '', $lastOrder->reference_number) + 1
            : 1;
        $data['reference_number'] = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
