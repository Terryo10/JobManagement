<?php

namespace App\Filament\Admin\Resources\PrazSubmissionResource\Pages;

use App\Filament\Admin\Resources\PrazSubmissionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPrazSubmission extends EditRecord
{
    protected static string $resource = PrazSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
