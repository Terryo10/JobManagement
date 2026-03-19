<?php

namespace App\Filament\Staff\Resources\PurchaseOrderResource\Pages;

use App\Filament\Staff\Resources\PurchaseOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPurchaseOrders extends ListRecords
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}