<?php

namespace App\Filament\Staff\Resources\WorkOrderMaterialResource\Pages;

use App\Filament\Shared\Actions\RequestDeletionAction;
use App\Filament\Staff\Resources\WorkOrderMaterialResource;
use Filament\Resources\Pages\EditRecord;

class EditWorkOrderMaterial extends EditRecord
{
    protected static string $resource = WorkOrderMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [RequestDeletionAction::make()];
    }
}
