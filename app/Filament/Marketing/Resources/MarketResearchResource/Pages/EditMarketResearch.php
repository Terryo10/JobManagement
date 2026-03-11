<?php

namespace App\Filament\Marketing\Resources\MarketResearchResource\Pages;

use App\Filament\Marketing\Resources\MarketResearchResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMarketResearch extends EditRecord
{
    protected static string $resource = MarketResearchResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
