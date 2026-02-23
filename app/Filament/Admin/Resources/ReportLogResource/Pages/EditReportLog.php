<?php

namespace App\Filament\Admin\Resources\ReportLogResource\Pages;

use App\Filament\Admin\Resources\ReportLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReportLog extends EditRecord
{
    protected static string $resource = ReportLogResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
