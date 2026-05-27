<?php

namespace App\Filament\Marketing\Resources\WorkOrderResource\Pages;

use App\Filament\Marketing\Resources\WorkOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkOrder extends EditRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }
}
