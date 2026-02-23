<?php

namespace App\Filament\Admin\Resources\StockLevelResource\Pages;

use App\Filament\Admin\Resources\StockLevelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStockLevel extends EditRecord
{
    protected static string $resource = StockLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
