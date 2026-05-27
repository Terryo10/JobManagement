<?php

namespace App\Filament\Marketing\Resources\InventoryRequisitionResource\Pages;

use App\Filament\Marketing\Resources\InventoryRequisitionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInventoryRequisitions extends ListRecords
{
    protected static string $resource = InventoryRequisitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
