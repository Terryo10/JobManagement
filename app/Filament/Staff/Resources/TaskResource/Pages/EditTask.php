<?php

namespace App\Filament\Staff\Resources\TaskResource\Pages;

use App\Filament\Staff\Resources\TaskResource;
use App\Filament\Staff\Resources\WorkOrderResource;
use App\Filament\Shared\Actions\RequestDeletionAction;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [RequestDeletionAction::make()];
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
