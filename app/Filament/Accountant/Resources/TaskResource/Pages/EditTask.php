<?php

namespace App\Filament\Accountant\Resources\TaskResource\Pages;

use App\Filament\Accountant\Resources\TaskResource;
use App\Filament\Accountant\Resources\WorkOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\ViewAction::make(), \App\Filament\Shared\Actions\RequestDeletionAction::make()];
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
