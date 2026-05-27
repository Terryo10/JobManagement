<?php

namespace App\Filament\Staff\Resources\DesignBriefResource\Pages;

use App\Filament\Staff\Resources\DesignBriefResource;
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
