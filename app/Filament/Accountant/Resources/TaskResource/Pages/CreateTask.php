<?php

namespace App\Filament\Accountant\Resources\TaskResource\Pages;

use App\Filament\Accountant\Resources\TaskResource;
use App\Filament\Accountant\Resources\WorkOrderResource;
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
