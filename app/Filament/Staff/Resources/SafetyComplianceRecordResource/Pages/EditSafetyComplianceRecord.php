<?php

namespace App\Filament\Staff\Resources\SafetyComplianceRecordResource\Pages;

use App\Filament\Shared\Actions\RequestDeletionAction;
use App\Filament\Staff\Resources\SafetyComplianceRecordResource;
use Filament\Resources\Pages\EditRecord;

class EditSafetyComplianceRecord extends EditRecord
{
    protected static string $resource = SafetyComplianceRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [RequestDeletionAction::make()];
    }
}
