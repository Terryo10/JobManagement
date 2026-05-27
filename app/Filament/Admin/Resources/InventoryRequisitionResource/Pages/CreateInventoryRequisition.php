<?php

namespace App\Filament\Admin\Resources\InventoryRequisitionResource\Pages;

use App\Filament\Admin\Resources\InventoryRequisitionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInventoryRequisition extends CreateRecord
{
    protected static string $resource = InventoryRequisitionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
