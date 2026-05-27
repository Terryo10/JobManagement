<?php

namespace App\Filament\Marketing\Resources\PrazSubmissionResource\Pages;

use App\Filament\Marketing\Resources\PrazSubmissionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPrazSubmission extends EditRecord
{
    protected static string $resource = PrazSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }
}
