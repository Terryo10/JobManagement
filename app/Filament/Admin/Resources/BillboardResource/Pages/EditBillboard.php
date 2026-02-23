<?php

namespace App\Filament\Admin\Resources\BillboardResource\Pages;

use App\Filament\Admin\Resources\BillboardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBillboard extends EditRecord
{
    protected static string $resource = BillboardResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
