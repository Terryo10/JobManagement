<?php

namespace App\Filament\Admin\Resources\WorkOrderNoteResource\Pages;

use App\Filament\Admin\Resources\WorkOrderNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkOrderNote extends EditRecord
{
    protected static string $resource = WorkOrderNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
