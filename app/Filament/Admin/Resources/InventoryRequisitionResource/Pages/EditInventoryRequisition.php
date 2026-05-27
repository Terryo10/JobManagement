<?php

namespace App\Filament\Admin\Resources\InventoryRequisitionResource\Pages;

use App\Filament\Admin\Resources\InventoryRequisitionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInventoryRequisition extends EditRecord
{
    protected static string $resource = InventoryRequisitionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
