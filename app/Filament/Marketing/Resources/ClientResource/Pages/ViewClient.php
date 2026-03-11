<?php

namespace App\Filament\Marketing\Resources\ClientResource\Pages;

use App\Filament\Marketing\Resources\ClientResource;
use Filament\Resources\Pages\ViewRecord;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\EditAction::make()];
    }
}
