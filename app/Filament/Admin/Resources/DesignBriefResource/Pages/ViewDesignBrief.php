<?php

namespace App\Filament\Admin\Resources\DesignBriefResource\Pages;

use App\Filament\Admin\Resources\DesignBriefResource;
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
