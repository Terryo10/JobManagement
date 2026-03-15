<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\WorkOrderMaterialResource\Pages;
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
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationGroup = 'Operations';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('work_order_id')->relationship('workOrder', 'reference_number')->searchable()->preload()->required(),
            Forms\Components\Select::make('material_id')->relationship('material', 'name')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('quantity_used')->numeric()->required(),
            Forms\Components\TextInput::make('unit_cost_at_time')->numeric()->prefix('USD'),
            Forms\Components\Select::make('logged_by')->relationship('loggedBy', 'name')->searchable()->preload()->required(),
            Forms\Components\DateTimePicker::make('logged_at')->required(),
            Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('workOrder.reference_number')->label('Work Order')->sortable(),
            Tables\Columns\TextColumn::make('material.name')->sortable(),
            Tables\Columns\TextColumn::make('quantity_used'),
            Tables\Columns\TextColumn::make('unit_cost_at_time')->money('USD'),
            Tables\Columns\TextColumn::make('loggedBy.name')->label('Logged By'),
            Tables\Columns\TextColumn::make('logged_at')->dateTime()->sortable(),
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
            'index'  => Pages\ListWorkOrderMaterials::route('/'),
            'create' => Pages\CreateWorkOrderMaterial::route('/create'),
            'edit'   => Pages\EditWorkOrderMaterial::route('/{record}/edit'),
        ];
    }
}
