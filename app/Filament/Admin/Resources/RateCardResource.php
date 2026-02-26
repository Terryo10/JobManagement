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
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('service_type')->required()->maxLength(255)
                    ->helperText('e.g., Billboard Installation, Graphic Design, Solar Panel Mounting'),
                Forms\Components\Select::make('category')
                    ->options([
                        'media' => 'Media', 'civil_works' => 'Civil Works',
                        'energy' => 'Energy', 'warehouse' => 'Warehouse',
                        'design' => 'Design', 'labour' => 'Labour',
                    ])->required(),
                Forms\Components\TextInput::make('unit')->required()->maxLength(50)
                    ->helperText('e.g., sqm, hour, unit, metre'),
                Forms\Components\TextInput::make('rate')->numeric()->required()->prefix('$'),
                Forms\Components\TextInput::make('currency')->default('USD')->maxLength(10),
                Forms\Components\Toggle::make('is_active')->default(true),
                Forms\Components\DatePicker::make('effective_from'),
                Forms\Components\DatePicker::make('effective_to'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('service_type')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('category')->badge()
                ->color(fn ($state) => match ($state) {
                    'media' => 'primary', 'civil_works' => 'warning', 'energy' => 'success',
                    'warehouse' => 'info', 'design' => 'purple', 'labour' => 'orange',
                    default => 'gray',
                }),
            Tables\Columns\TextColumn::make('rate')->money('usd')->sortable(),
            Tables\Columns\TextColumn::make('unit'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
            Tables\Columns\TextColumn::make('effective_from')->date(),
            Tables\Columns\TextColumn::make('effective_to')->date(),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('category')->options([
                'media' => 'Media', 'civil_works' => 'Civil Works', 'energy' => 'Energy',
                'warehouse' => 'Warehouse', 'design' => 'Design', 'labour' => 'Labour',
            ]),
            Tables\Filters\TernaryFilter::make('is_active'),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\ReplicateAction::make()
                ->label('Duplicate')
                ->excludeAttributes(['created_at', 'updated_at'])
                ->successNotificationTitle('Rate card duplicated'),
        ])
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
