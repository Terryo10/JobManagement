<?php

namespace App\Filament\Admin\Resources\WorkOrderNoteResource\Pages;

use App\Filament\Admin\Resources\WorkOrderNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkOrderNotes extends ListRecords
{
    protected static string $resource = WorkOrderNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
