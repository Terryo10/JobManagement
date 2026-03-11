<?php

namespace App\Filament\Marketing\Resources\MarketResearchResource\Pages;

use App\Filament\Marketing\Resources\MarketResearchResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMarketResearch extends CreateRecord
{
    protected static string $resource = MarketResearchResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['researched_by'] = auth()->id();
        return $data;
    }
}
