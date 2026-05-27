<?php

namespace App\Filament\Staff\Resources\InventoryRequisitionResource\Pages;

use App\Filament\Staff\Resources\InventoryRequisitionResource;
use Filament\Resources\Pages\EditRecord;

class EditInventoryRequisition extends EditRecord
{
    protected static string $resource = InventoryRequisitionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
