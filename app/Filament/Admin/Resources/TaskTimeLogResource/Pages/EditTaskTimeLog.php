<?php

namespace App\Filament\Admin\Resources\TaskTimeLogResource\Pages;

use App\Filament\Admin\Resources\TaskTimeLogResource;
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
