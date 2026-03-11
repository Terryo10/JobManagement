<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\StaffCapacitySettingResource\Pages;
use App\Models\StaffCapacitySetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StaffCapacitySettingResource extends Resource
{
    protected static ?string $model = StaffCapacitySetting::class;
    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static ?string $navigationGroup = 'HR';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('role_name')->required()->maxLength(100)->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('max_concurrent_tasks')->numeric()->required()->default(5),
            Forms\Components\TextInput::make('max_weekly_hours')->numeric()->required()->default(40),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('role_name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('max_concurrent_tasks')->sortable(),
            Tables\Columns\TextColumn::make('max_weekly_hours')->sortable(),
            Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
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
            'index'  => Pages\ListStaffCapacitySettings::route('/'),
            'create' => Pages\CreateStaffCapacitySetting::route('/create'),
            'edit'   => Pages\EditStaffCapacitySetting::route('/{record}/edit'),
        ];
    }
}
