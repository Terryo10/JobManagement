<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BillboardResource\Pages;
use App\Models\Billboard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BillboardResource extends Resource
{
    protected static ?string $model = Billboard::class;
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?string $navigationLabel = 'Billboards';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('location_description')->required()->maxLength(255)->columnSpanFull(),
            Forms\Components\TextInput::make('latitude')->numeric(),
            Forms\Components\TextInput::make('longitude')->numeric(),
            Forms\Components\TextInput::make('size')->maxLength(50),
            Forms\Components\TextInput::make('type')->maxLength(100),
            Forms\Components\Select::make('status')->options(['available' => 'Available', 'occupied' => 'Occupied', 'maintenance' => 'Maintenance', 'inactive' => 'Inactive'])->default('available')->required(),
            Forms\Components\TextInput::make('monthly_rate')->numeric()->prefix('USD'),
            Forms\Components\DatePicker::make('next_maintenance_date'),
            Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('location_description')->limit(50),
            Tables\Columns\TextColumn::make('size'),
            Tables\Columns\TextColumn::make('type'),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) { 'available' => 'success', 'occupied' => 'info', 'maintenance' => 'warning', 'inactive' => 'gray', default => 'gray' }),
            Tables\Columns\TextColumn::make('monthly_rate')->money('USD')->sortable(),
            Tables\Columns\TextColumn::make('next_maintenance_date')->date()->sortable(),
        ])
        ->filters([Tables\Filters\SelectFilter::make('status')->options(['available' => 'Available', 'occupied' => 'Occupied', 'maintenance' => 'Maintenance', 'inactive' => 'Inactive'])])
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
            'index'  => Pages\ListBillboards::route('/'),
            'create' => Pages\CreateBillboard::route('/create'),
            'edit'   => Pages\EditBillboard::route('/{record}/edit'),
        ];
    }
}
