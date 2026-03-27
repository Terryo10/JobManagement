<?php

namespace App\Filament\Staff\Resources\TaskResource\Pages;

use App\Filament\Staff\Resources\TaskResource;
use App\Filament\Staff\Resources\WorkOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    protected function getRedirectUrl(): string
    {
        $workOrderId = $this->record->work_order_id;
        if ($workOrderId) {
            return WorkOrderResource::getUrl('view', ['record' => $workOrderId]);
        }
        return $this->getResource()::getUrl('index');
    }
}
