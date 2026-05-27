<?php

namespace App\Filament\Admin\Resources\PrazSubmissionResource\Pages;

use App\Filament\Admin\Resources\PrazSubmissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPrazSubmissions extends ListRecords
{
    protected static string $resource = PrazSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
