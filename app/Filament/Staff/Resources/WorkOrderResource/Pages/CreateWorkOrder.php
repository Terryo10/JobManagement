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



        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
