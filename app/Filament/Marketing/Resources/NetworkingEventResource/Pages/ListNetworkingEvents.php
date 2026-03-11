<?php

namespace App\Filament\Marketing\Resources\NetworkingEventResource\Pages;

use App\Filament\Marketing\Resources\NetworkingEventResource;
use Filament\Resources\Pages\ListRecords;

class ListNetworkingEvents extends ListRecords
{
    protected static string $resource = NetworkingEventResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}
