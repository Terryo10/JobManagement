<?php

namespace App\Filament\Marketing\Resources\TaskResource\Pages;

use App\Filament\Marketing\Resources\TaskResource;
use App\Filament\Marketing\Resources\WorkOrderResource;
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
