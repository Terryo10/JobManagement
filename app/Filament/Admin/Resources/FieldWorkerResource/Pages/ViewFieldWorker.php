<?php

namespace App\Filament\Admin\Resources\FieldWorkerResource\Pages;

use App\Filament\Admin\Resources\FieldWorkerResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewFieldWorker extends ViewRecord
{
    protected static string $resource = FieldWorkerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
