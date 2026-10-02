<?php

namespace App\Filament\Accountant\Resources;

use App\Filament\Accountant\Resources\SalesOrderResource\Pages;
use App\Filament\Shared\Resources\BaseSalesOrderResource;

class SalesOrderResource extends BaseSalesOrderResource
{
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesOrders::route('/'),
            'view' => Pages\ViewSalesOrder::route('/{record}'),
            'edit' => Pages\EditSalesOrder::route('/{record}/edit'),
        ];
    }
}
