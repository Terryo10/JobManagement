<?php

namespace App\Filament\Staff\Resources\WorkOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MaterialsRelationManager extends RelationManager
{
    protected static string $relationship = 'materials';

    protected static ?string $title = 'Materials Used';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('material_id')
                ->relationship('material', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->label('Material'),
            Forms\Components\TextInput::make('quantity_used')
                ->numeric()
                ->required()
                ->minValue(0.01)
                ->label('Quantity Used'),
            Forms\Components\TextInput::make('unit_cost_at_time')
                ->numeric()
                ->prefix('USD')
                ->label('Unit Cost at Time of Use'),
            Forms\Components\Hidden::make('logged_by')
                ->default(fn () => auth()->id()),
            Forms\Components\Hidden::make('logged_at')
                ->default(fn () => now()),
            Forms\Components\Textarea::make('notes')
                ->rows(2)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('material.name')
                    ->label('Material')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity_used')
                    ->label('Qty Used'),
                Tables\Columns\TextColumn::make('unit_cost_at_time')
                    ->label('Unit Cost')
                    ->money('USD')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('loggedBy.name')
                    ->label('Logged By')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('logged_at')
                    ->label('Logged At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Log Material Used'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                \App\Filament\Shared\Actions\RequestDeletionTableAction::make(),
            ])
            ->emptyStateHeading('No materials logged yet')
            ->emptyStateDescription('Record any materials or supplies consumed on this job.')
            ->emptyStateIcon('heroicon-o-cube');
    }
}
