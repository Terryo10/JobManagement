<?php

namespace App\Filament\Marketing\Resources\PurchaseOrderResource\Pages;

use App\Filament\Marketing\Resources\PurchaseOrderResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPurchaseOrder extends ViewRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\EditAction::make()];
    }
}
