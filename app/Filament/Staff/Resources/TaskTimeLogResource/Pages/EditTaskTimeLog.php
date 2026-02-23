<?php

namespace App\Filament\Staff\Resources\TaskTimeLogResource\Pages;

use App\Filament\Staff\Resources\TaskTimeLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaskTimeLog extends EditRecord
{
    protected static string $resource = TaskTimeLogResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
