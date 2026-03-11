<?php

namespace App\Filament\Staff\Resources\AllWorkOrdersResource\Pages;

use App\Filament\Staff\Resources\AllWorkOrdersResource;
use Filament\Resources\Pages\ListRecords;

class ListAllWorkOrders extends ListRecords
{
    protected static string $resource = AllWorkOrdersResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
