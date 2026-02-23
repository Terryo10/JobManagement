<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\StaffAvailabilityResource\Pages;
use App\Models\StaffAvailability;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StaffAvailabilityResource extends Resource
{
    protected static ?string $model = StaffAvailability::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'My Availability';
    protected static ?string $navigationGroup = 'My Work';
    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Hidden::make('user_id')->default(fn () => auth()->id()),
            Forms\Components\DatePicker::make('unavailable_from')->required(),
            Forms\Components\DatePicker::make('unavailable_to')->required(),
            Forms\Components\Select::make('reason')->options(['leave' => 'Leave', 'field_deployment' => 'Field Deployment', 'training' => 'Training', 'other' => 'Other']),
            Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('unavailable_from')->date()->sortable(),
            Tables\Columns\TextColumn::make('unavailable_to')->date()->sortable(),
            Tables\Columns\TextColumn::make('reason')->badge(),
            Tables\Columns\TextColumn::make('approvedBy.name')->label('Approved By'),
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
            'index'  => Pages\ListStaffAvailabilities::route('/'),
            'create' => Pages\CreateStaffAvailability::route('/create'),
            'edit'   => Pages\EditStaffAvailability::route('/{record}/edit'),
        ];
    }
}
