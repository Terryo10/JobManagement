<?php

namespace App\Filament\Marketing\Resources\DesignBriefResource\Pages;

use App\Filament\Marketing\Resources\DesignBriefResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDesignBriefs extends ListRecords
{
    protected static string $resource = DesignBriefResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
