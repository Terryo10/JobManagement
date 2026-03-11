<?php

namespace App\Filament\Marketing\Resources\BusinessReportResource\Pages;

use App\Filament\Marketing\Resources\BusinessReportResource;
use Filament\Resources\Pages\ListRecords;

class ListBusinessReports extends ListRecords
{
    protected static string $resource = BusinessReportResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}
