<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MaterialResource\Pages;
use App\Models\Material;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MaterialResource extends Resource
{
    protected static ?string $model = Material::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Warehouse';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('sku')->required()->maxLength(100)->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('category')->maxLength(100),
            Forms\Components\TextInput::make('unit')->required()->maxLength(50),
            Forms\Components\TextInput::make('minimum_stock_level')->numeric()->default(0),
            Forms\Components\TextInput::make('reorder_quantity')->numeric(),
            Forms\Components\TextInput::make('unit_cost')->numeric()->prefix('USD'),
            Forms\Components\Select::make('preferred_supplier_id')->relationship('preferredSupplier', 'name')->searchable()->preload()->label('Preferred Supplier'),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('sku')->searchable(),
            Tables\Columns\TextColumn::make('category')->badge(),
            Tables\Columns\TextColumn::make('unit'),
            Tables\Columns\TextColumn::make('stockLevel.current_quantity')->label('In Stock'),
            Tables\Columns\TextColumn::make('minimum_stock_level')->label('Min Stock'),
            Tables\Columns\TextColumn::make('unit_cost')->money('USD'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])
        ->filters([Tables\Filters\TernaryFilter::make('is_active')])
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
            'index'  => Pages\ListMaterials::route('/'),
            'create' => Pages\CreateMaterial::route('/create'),
            'edit'   => Pages\EditMaterial::route('/{record}/edit'),
        ];
    }
}
