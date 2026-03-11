<?php

namespace App\Filament\Marketing\Resources\LeadCommunicationResource\Pages;

use App\Filament\Marketing\Resources\LeadCommunicationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLeadCommunication extends CreateRecord
{
    protected static string $resource = LeadCommunicationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }
}
