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
        $workOrder = $this->record->workOrder;
        if ($workOrder) {
            if ($workOrder->claimed_by === auth()->id()) {
                return WorkOrderResource::getUrl('view', ['record' => $workOrder->id]);
            }
            return \App\Filament\Staff\Resources\AllWorkOrdersResource::getUrl('view', ['record' => $workOrder->id]);
        }
        return $this->getResource()::getUrl('index');
    }
}
