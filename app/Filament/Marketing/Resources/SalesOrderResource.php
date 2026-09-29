<?php

namespace App\Filament\Marketing\Resources;

use App\Filament\Marketing\Resources\SalesOrderResource\Pages;
use App\Filament\Shared\Resources\BaseSalesOrderResource;

class SalesOrderResource extends BaseSalesOrderResource
{
    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 2;

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesOrders::route('/'),
            'view' => Pages\ViewSalesOrder::route('/{record}'),
            'edit' => Pages\EditSalesOrder::route('/{record}/edit'),
        ];
    }
}
