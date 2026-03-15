<?php

namespace App\Filament\Staff\Resources\EquipmentResource\Pages;

use App\Filament\Staff\Resources\EquipmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEquipment extends ListRecords
{
    protected static string $resource = EquipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => auth()->user()?->hasAnyRole(['manager', 'dept_head', 'super_admin'])),
        ];
    }
}
