<?php

namespace App\Filament\Staff\Resources\InventoryRequisitionResource\Pages;

use App\Filament\Staff\Resources\InventoryRequisitionResource;
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
