<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\StockLevelResource\Pages;
use App\Models\StockLevel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StockLevelResource extends Resource
{
    protected static ?string $model = StockLevel::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Warehouse';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('material_id')->relationship('material', 'name')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('current_quantity')->numeric()->required()->default(0),
            Forms\Components\DateTimePicker::make('last_updated'),
            Forms\Components\Select::make('last_updated_by')->relationship('lastUpdatedBy', 'name')->searchable()->preload(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('material.name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('material.sku')->label('SKU'),
            Tables\Columns\TextColumn::make('current_quantity')->sortable(),
            Tables\Columns\TextColumn::make('material.minimum_stock_level')->label('Min Level'),
            Tables\Columns\TextColumn::make('lastUpdatedBy.name')->label('Updated By'),
            Tables\Columns\TextColumn::make('last_updated')->dateTime()->sortable(),
        ])
        ->actions([Tables\Actions\EditAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStockLevels::route('/'),
            'create' => Pages\CreateStockLevel::route('/create'),
            'edit'   => Pages\EditStockLevel::route('/{record}/edit'),
        ];
    }
}
