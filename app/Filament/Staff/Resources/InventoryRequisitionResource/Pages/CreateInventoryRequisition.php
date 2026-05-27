<?php

namespace App\Filament\Staff\Resources\InventoryRequisitionResource\Pages;

use App\Filament\Staff\Resources\InventoryRequisitionResource;
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
        // Always stamp the current user as requester
        $data['requested_by'] = auth()->id();
        return $data;
    }
}
