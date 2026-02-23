<?php

namespace App\Filament\Admin\Resources\RateCardResource\Pages;

use App\Filament\Admin\Resources\RateCardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRateCard extends EditRecord
{
    protected static string $resource = RateCardResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
