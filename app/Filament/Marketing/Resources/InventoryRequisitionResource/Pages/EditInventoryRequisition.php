<?php

namespace App\Filament\Marketing\Resources\InventoryRequisitionResource\Pages;

use App\Filament\Marketing\Resources\InventoryRequisitionResource;
use Filament\Resources\Pages\EditRecord;

class EditInventoryRequisition extends EditRecord
{
    protected static string $resource = InventoryRequisitionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
