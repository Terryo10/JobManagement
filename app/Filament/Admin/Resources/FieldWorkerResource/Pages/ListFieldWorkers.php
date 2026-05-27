<?php

namespace App\Filament\Admin\Resources\FieldWorkerResource\Pages;

use App\Filament\Admin\Resources\FieldWorkerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFieldWorkers extends ListRecords
{
    protected static string $resource = FieldWorkerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
