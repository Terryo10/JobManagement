<?php

namespace App\Filament\Admin\Resources\WorkOrderMaterialResource\Pages;

use App\Filament\Admin\Resources\WorkOrderMaterialResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkOrderMaterials extends ListRecords
{
    protected static string $resource = WorkOrderMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
