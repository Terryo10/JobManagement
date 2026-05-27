<?php

namespace App\Filament\Accountant\Resources\StockLevelResource\Pages;

use App\Filament\Accountant\Resources\StockLevelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStockLevels extends ListRecords
{
    protected static string $resource = StockLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
