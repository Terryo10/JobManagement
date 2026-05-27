<?php

namespace App\Filament\Marketing\Resources\PrazSubmissionResource\Pages;

use App\Filament\Marketing\Resources\PrazSubmissionResource;
use Filament\Resources\Pages\ListRecords;

class ListPrazSubmissions extends ListRecords
{
    protected static string $resource = PrazSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
