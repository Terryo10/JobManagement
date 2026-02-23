<?php

namespace App\Filament\Admin\Resources\ReportLogResource\Pages;

use App\Filament\Admin\Resources\ReportLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReportLogs extends ListRecords
{
    protected static string $resource = ReportLogResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
