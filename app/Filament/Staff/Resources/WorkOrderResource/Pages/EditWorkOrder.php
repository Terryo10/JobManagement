<?php

namespace App\Filament\Staff\Resources\WorkOrderResource\Pages;

use App\Filament\Shared\Actions\RequestDeletionAction;
use App\Filament\Staff\Resources\WorkOrderResource;
use Filament\Resources\Pages\EditRecord;

class EditWorkOrder extends EditRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [RequestDeletionAction::make()];
    }
}
