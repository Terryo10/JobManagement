<?php

namespace App\Filament\Accountant\Resources\MaterialResource\Pages;

use App\Filament\Accountant\Resources\MaterialResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMaterials extends ListRecords
{
    protected static string $resource = MaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
