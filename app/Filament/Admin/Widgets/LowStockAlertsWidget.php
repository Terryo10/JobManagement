<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Material;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockAlertsWidget extends BaseWidget
{
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 1;
    protected static ?string $heading = 'Low Stock Alerts';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Material::query()
                    ->where('is_active', true)
                    ->whereHas('stockLevel', function ($q) {
                        $q->whereRaw('current_quantity <= materials.minimum_stock_level');
                    })
                    ->with('stockLevel')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Material')->limit(25),
                Tables\Columns\TextColumn::make('stockLevel.current_quantity')
                    ->label('In Stock')
                    ->numeric(0)
                    ->color('danger'),
                Tables\Columns\TextColumn::make('minimum_stock_level')
                    ->label('Min.')
                    ->numeric(0),
                Tables\Columns\TextColumn::make('unit'),
            ])
            ->paginated(false)
            ->emptyStateHeading('All stock levels OK')
            ->emptyStateDescription('No materials are below minimum stock levels')
            ->emptyStateIcon('heroicon-o-check-badge');
    }
}
