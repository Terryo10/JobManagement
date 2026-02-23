<?php

namespace App\Filament\Admin\Resources\SafetyComplianceRecordResource\Pages;

use App\Filament\Admin\Resources\SafetyComplianceRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSafetyComplianceRecord extends EditRecord
{
    protected static string $resource = SafetyComplianceRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
