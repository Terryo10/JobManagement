<?php

namespace App\Filament\Admin\Resources\TaskResource\Pages;

use App\Filament\Admin\Resources\TaskResource;
use App\Filament\Admin\Resources\WorkOrderResource;
use App\Jobs\SendFieldWorkerNotificationJob;
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

    /**
     * After the task and its pivot rows are saved, queue a notification
     * job for every field worker attached to this task.
     */
    protected function afterCreate(): void
    {
        foreach ($this->record->fieldWorkers as $worker) {
            SendFieldWorkerNotificationJob::dispatch($worker->id, $this->record->id)
                ->onQueue('notifications');
        }
    }
}
