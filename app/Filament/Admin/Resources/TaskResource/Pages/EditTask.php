<?php

namespace App\Filament\Admin\Resources\TaskResource\Pages;

use App\Filament\Admin\Resources\TaskResource;
use App\Filament\Admin\Resources\WorkOrderResource;
use App\Jobs\SendFieldWorkerNotificationJob;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Collection;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    /** Snapshot of field worker IDs before the form is saved. */
    protected Collection $fieldWorkerIdsBefore;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        $workOrderId = $this->record->work_order_id;
        if ($workOrderId) {
            return WorkOrderResource::getUrl('view', ['record' => $workOrderId]);
        }
        return $this->getResource()::getUrl('index');
    }

    /**
     * Capture the current field worker IDs before the form data is persisted.
     * This lets us diff afterwards to find only newly-added workers.
     */
    protected function beforeSave(): void
    {
        $this->fieldWorkerIdsBefore = $this->record->fieldWorkers()->pluck('field_workers.id');
    }

    /**
     * After save, dispatch notifications only for workers that were
     * not present before — avoids re-notifying existing assignees.
     */
    protected function afterSave(): void
    {
        $afterIds  = $this->record->fieldWorkers()->pluck('field_workers.id');
        $newlyAdded = $afterIds->diff($this->fieldWorkerIdsBefore);

        foreach ($newlyAdded as $workerId) {
            SendFieldWorkerNotificationJob::dispatch($workerId, $this->record->id)
                ->onQueue('notifications');
        }
    }
}
