<?php

namespace App\Filament\Admin\Resources\AdminTaskResource\Pages;

use App\Filament\Admin\Resources\AdminTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdminTask extends EditRecord
{
    protected static string $resource = AdminTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
