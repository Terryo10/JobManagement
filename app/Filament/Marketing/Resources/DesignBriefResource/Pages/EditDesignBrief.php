<?php

namespace App\Filament\Marketing\Resources\DesignBriefResource\Pages;

use App\Filament\Marketing\Resources\DesignBriefResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDesignBrief extends EditRecord
{
    protected static string $resource = DesignBriefResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
