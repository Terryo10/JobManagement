<?php

namespace App\Filament\Marketing\Resources\InventoryRequisitionResource\Pages;

use App\Filament\Marketing\Resources\InventoryRequisitionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInventoryRequisition extends CreateRecord
{
    protected static string $resource = InventoryRequisitionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requested_by'] = auth()->id();
        return $data;
    }
}
