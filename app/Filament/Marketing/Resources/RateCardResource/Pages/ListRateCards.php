<?php

namespace App\Filament\Marketing\Resources\RateCardResource\Pages;

use App\Filament\Marketing\Resources\RateCardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRateCards extends ListRecords
{
    protected static string $resource = RateCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
