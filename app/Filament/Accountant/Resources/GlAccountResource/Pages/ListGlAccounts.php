<?php

namespace App\Filament\Accountant\Resources\GlAccountResource\Pages;

use App\Filament\Accountant\Resources\GlAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGlAccounts extends ListRecords
{
    protected static string $resource = GlAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
