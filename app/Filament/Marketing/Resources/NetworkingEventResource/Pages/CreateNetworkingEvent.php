<?php

namespace App\Filament\Marketing\Resources\NetworkingEventResource\Pages;

use App\Filament\Marketing\Resources\NetworkingEventResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNetworkingEvent extends CreateRecord
{
    protected static string $resource = NetworkingEventResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        return $data;
    }
}
