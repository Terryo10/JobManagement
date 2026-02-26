<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\EquipmentResource\Pages;
use App\Models\Equipment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationGroup = 'Division';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('serial_number')->maxLength(100)->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('category')->required()->maxLength(100),
            Forms\Components\Select::make('division')->options(['civil_works' => 'Civil Works', 'energy' => 'Energy'])->required(),
            Forms\Components\Select::make('status')->options(['available' => 'Available', 'in_use' => 'In Use', 'maintenance' => 'Maintenance', 'retired' => 'Retired'])->default('available')->required(),
            Forms\Components\Select::make('current_work_order_id')->relationship('currentWorkOrder', 'reference_number')->searchable()->preload()->label('Current Work Order'),
            Forms\Components\DatePicker::make('purchase_date'),
            Forms\Components\DatePicker::make('next_maintenance_date'),
            Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('serial_number')->searchable(),
            Tables\Columns\TextColumn::make('category'),
            Tables\Columns\TextColumn::make('division')->badge(),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) { 'available' => 'success', 'in_use' => 'info', 'maintenance' => 'warning', 'retired' => 'gray', default => 'gray' }),
            Tables\Columns\TextColumn::make('next_maintenance_date')->date()->sortable(),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')->options(['available' => 'Available', 'in_use' => 'In Use', 'maintenance' => 'Maintenance', 'retired' => 'Retired']),
            Tables\Filters\SelectFilter::make('division')->options(['civil_works' => 'Civil Works', 'energy' => 'Energy']),
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
            'index'  => Pages\ListEquipment::route('/'),
            'create' => Pages\CreateEquipment::route('/create'),
            'edit'   => Pages\EditEquipment::route('/{record}/edit'),
        ];
    }
}
