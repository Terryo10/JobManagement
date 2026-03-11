<?php

namespace App\Filament\Marketing\Resources\MarketResearchResource\Pages;

use App\Filament\Marketing\Resources\MarketResearchResource;
use Filament\Resources\Pages\ListRecords;

class ListMarketResearch extends ListRecords
{
    protected static string $resource = MarketResearchResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}
