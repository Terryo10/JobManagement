<?php

namespace App\Filament\Admin\Resources\AdminTaskResource\Pages;

use App\Filament\Admin\Resources\AdminTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAdminTask extends ViewRecord
{
    protected static string $resource = AdminTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
