<?php

namespace App\Filament\Marketing\Resources\RateCardResource\Pages;

use App\Filament\Marketing\Resources\RateCardResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRateCard extends CreateRecord
{
    protected static string $resource = RateCardResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        return $data;
    }
}
