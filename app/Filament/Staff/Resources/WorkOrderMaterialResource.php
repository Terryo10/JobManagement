<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\WorkOrderMaterialResource\Pages;
use App\Models\WorkOrderMaterial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkOrderMaterialResource extends Resource
{
    protected static ?string $model = WorkOrderMaterial::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Material Usage';
    protected static ?string $navigationGroup = 'My Work';
    protected static ?int $navigationSort = 5;
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('work_order_id')->relationship('workOrder', 'reference_number')->searchable()->preload()->required(),
            Forms\Components\Select::make('material_id')->relationship('material', 'name')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('quantity_used')->numeric()->required(),
            Forms\Components\TextInput::make('unit_cost_at_time')->numeric()->prefix('USD'),
            Forms\Components\Hidden::make('logged_by')->default(fn () => auth()->id()),
            Forms\Components\Hidden::make('logged_at')->default(fn () => now()),
            Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('workOrder.reference_number')->label('Work Order'),
            Tables\Columns\TextColumn::make('material.name'),
            Tables\Columns\TextColumn::make('quantity_used'),
            Tables\Columns\TextColumn::make('logged_at')->dateTime()->sortable(),
        ])
        ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListWorkOrderMaterials::route('/'),
            'create' => Pages\CreateWorkOrderMaterial::route('/create'),
            'edit'   => Pages\EditWorkOrderMaterial::route('/{record}/edit'),
        ];
    }
}
