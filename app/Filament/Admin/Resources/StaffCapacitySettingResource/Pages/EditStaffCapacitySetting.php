<?php

namespace App\Filament\Admin\Resources\StaffCapacitySettingResource\Pages;

use App\Filament\Admin\Resources\StaffCapacitySettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStaffCapacitySetting extends EditRecord
{
    protected static string $resource = StaffCapacitySettingResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
