<?php

namespace App\Filament\Admin\Resources\ReportLogResource\Pages;

use App\Filament\Admin\Resources\ReportLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReportLog extends CreateRecord
{
    protected static string $resource = ReportLogResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['generated_by'] = auth()->id();

        return $data;
    }
}
