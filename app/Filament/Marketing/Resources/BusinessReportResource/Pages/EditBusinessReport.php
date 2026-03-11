<?php

namespace App\Filament\Marketing\Resources\BusinessReportResource\Pages;

use App\Filament\Marketing\Resources\BusinessReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBusinessReport extends EditRecord
{
    protected static string $resource = BusinessReportResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
