<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\StaffAvailabilityResource\Pages;
use App\Models\StaffAvailability;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StaffAvailabilityResource extends Resource
{
    protected static ?string $model = StaffAvailability::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Staff Management';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')->relationship('user', 'name')->searchable()->preload()->required(),
            Forms\Components\DatePicker::make('unavailable_from')->required(),
            Forms\Components\DatePicker::make('unavailable_to')->required(),
            Forms\Components\Select::make('reason')->options(['leave' => 'Leave', 'field_deployment' => 'Field Deployment', 'training' => 'Training', 'other' => 'Other']),
            Forms\Components\Textarea::make('notes')->rows(3),
            Forms\Components\Select::make('approved_by')->relationship('approvedBy', 'name')->searchable()->preload()->label('Approved By'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('user.name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('unavailable_from')->date()->sortable(),
            Tables\Columns\TextColumn::make('unavailable_to')->date()->sortable(),
            Tables\Columns\TextColumn::make('reason')->badge(),
            Tables\Columns\TextColumn::make('approvedBy.name')->label('Approved By'),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([Tables\Filters\SelectFilter::make('reason')->options(['leave' => 'Leave', 'field_deployment' => 'Field Deployment', 'training' => 'Training', 'other' => 'Other'])])
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
            'index'  => Pages\ListStaffAvailabilities::route('/'),
            'create' => Pages\CreateStaffAvailability::route('/create'),
            'edit'   => Pages\EditStaffAvailability::route('/{record}/edit'),
        ];
    }
}
