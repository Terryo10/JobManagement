<?php

namespace App\Filament\Admin\Resources\AdminTaskResource\Pages;

use App\Filament\Admin\Resources\AdminTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdminTasks extends ListRecords
{
    protected static string $resource = AdminTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
