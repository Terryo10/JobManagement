<?php

namespace App\Filament\Marketing\Resources\BusinessReportResource\Pages;

use App\Filament\Marketing\Resources\BusinessReportResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBusinessReport extends CreateRecord
{
    protected static string $resource = BusinessReportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['prepared_by'] = auth()->id();
        return $data;
    }
}
