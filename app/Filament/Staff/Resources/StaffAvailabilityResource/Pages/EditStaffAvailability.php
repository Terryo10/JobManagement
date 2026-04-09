<?php

namespace App\Filament\Staff\Resources\StaffAvailabilityResource\Pages;

use App\Filament\Shared\Actions\RequestDeletionAction;
use App\Filament\Staff\Resources\StaffAvailabilityResource;
use Filament\Resources\Pages\EditRecord;

class EditStaffAvailability extends EditRecord
{
    protected static string $resource = StaffAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [RequestDeletionAction::make()];
    }
}
