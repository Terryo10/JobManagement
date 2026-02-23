<?php

namespace App\Filament\Admin\Resources\ScheduledReportResource\Pages;

use App\Filament\Admin\Resources\ScheduledReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditScheduledReport extends EditRecord
{
    protected static string $resource = ScheduledReportResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
