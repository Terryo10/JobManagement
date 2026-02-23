<?php

namespace App\Filament\Client\Resources\WorkOrderResource\Pages;

use App\Filament\Client\Resources\WorkOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkOrders extends ListRecords
{
    protected static string $resource = WorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
