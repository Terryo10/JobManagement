<?php

namespace App\Filament\Marketing\Resources\DesignBriefResource\Pages;

use App\Filament\Marketing\Resources\DesignBriefResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDesignBrief extends ViewRecord
{
    protected static string $resource = DesignBriefResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
