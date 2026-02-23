<?php

namespace App\Filament\Admin\Resources\StaffCapacitySettingResource\Pages;

use App\Filament\Admin\Resources\StaffCapacitySettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStaffCapacitySettings extends ListRecords
{
    protected static string $resource = StaffCapacitySettingResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
