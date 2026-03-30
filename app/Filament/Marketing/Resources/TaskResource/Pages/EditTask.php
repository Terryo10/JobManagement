<?php

namespace App\Filament\Marketing\Resources\TaskResource\Pages;

use App\Filament\Marketing\Resources\TaskResource;
use App\Filament\Marketing\Resources\WorkOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [\App\Filament\Shared\Actions\RequestDeletionAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        $workOrderId = $this->record->work_order_id;
        if ($workOrderId) {
            return WorkOrderResource::getUrl('view', ['record' => $workOrderId]);
        }
        return $this->getResource()::getUrl('index');
    }
}
