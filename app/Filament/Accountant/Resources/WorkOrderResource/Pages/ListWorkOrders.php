<?php

namespace App\Filament\Accountant\Resources\WorkOrderResource\Pages;

use App\Filament\Accountant\Resources\WorkOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkOrders extends ListRecords
{
    protected static string $resource = WorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
