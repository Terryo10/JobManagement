<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RateCardResource\Pages;
use App\Models\RateCard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RateCardResource extends Resource
{
    protected static ?string $model = RateCard::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('service_type')->required()->maxLength(255),
            Forms\Components\Select::make('category')->options(['media' => 'Media', 'civil_works' => 'Civil Works', 'energy' => 'Energy'])->required(),
            Forms\Components\TextInput::make('unit')->required()->maxLength(50),
            Forms\Components\TextInput::make('rate')->numeric()->required()->prefix('USD'),
            Forms\Components\TextInput::make('currency')->default('USD')->maxLength(10),
            Forms\Components\DatePicker::make('effective_from')->required(),
            Forms\Components\DatePicker::make('effective_to'),
            Forms\Components\Toggle::make('is_active')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('service_type')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('category')->badge(),
            Tables\Columns\TextColumn::make('unit'),
            Tables\Columns\TextColumn::make('rate')->money('USD')->sortable(),
            Tables\Columns\TextColumn::make('effective_from')->date()->sortable(),
            Tables\Columns\TextColumn::make('effective_to')->date(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('category')->options(['media' => 'Media', 'civil_works' => 'Civil Works', 'energy' => 'Energy']),
            Tables\Filters\TernaryFilter::make('is_active'),
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
            'index'  => Pages\ListRateCards::route('/'),
            'create' => Pages\CreateRateCard::route('/create'),
            'edit'   => Pages\EditRateCard::route('/{record}/edit'),
        ];
    }
}
