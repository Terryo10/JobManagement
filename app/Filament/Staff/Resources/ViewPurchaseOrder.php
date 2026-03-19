<?php

namespace App\Filament\Staff\Resources\PurchaseOrderResource\Pages;

use App\Filament\Staff\Resources\PurchaseOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPurchaseOrder extends ViewRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}