<?php

namespace App\Filament\Marketing\Resources\PersonalFileResource\Pages;

use App\Filament\Marketing\Resources\PersonalFileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPersonalFile extends EditRecord
{
    protected static string $resource = PersonalFileResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
