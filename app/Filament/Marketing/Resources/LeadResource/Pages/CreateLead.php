<?php

namespace App\Filament\Marketing\Resources\LeadResource\Pages;

use App\Filament\Marketing\Resources\LeadResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLead extends CreateRecord
{
    protected static string $resource = LeadResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        return $data;
    }
}
