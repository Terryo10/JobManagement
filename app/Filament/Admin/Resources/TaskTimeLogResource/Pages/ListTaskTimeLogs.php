<?php

namespace App\Filament\Admin\Resources\TaskTimeLogResource\Pages;

use App\Filament\Admin\Resources\TaskTimeLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTaskTimeLogs extends ListRecords
{
    protected static string $resource = TaskTimeLogResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
