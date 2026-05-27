<?php

namespace App\Filament\Marketing\Resources\PrazSubmissionResource\Pages;

use App\Filament\Marketing\Resources\PrazSubmissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPrazSubmission extends ViewRecord
{
    protected static string $resource = PrazSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
