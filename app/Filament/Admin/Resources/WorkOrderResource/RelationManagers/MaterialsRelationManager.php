<?php

namespace App\Filament\Admin\Resources\WorkOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MaterialsRelationManager extends RelationManager
{
    protected static string $relationship = 'materials';
    protected static ?string $title = 'Materials';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('material_id')
                ->relationship('material', 'name')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('quantity_required')->numeric()->required()->minValue(1),
            Forms\Components\TextInput::make('quantity_used')->numeric()->default(0),
            Forms\Components\TextInput::make('unit_cost')->numeric()->prefix('$'),
            Forms\Components\Textarea::make('notes')->rows(2)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('material.name')->searchable(),
                Tables\Columns\TextColumn::make('quantity_required')->numeric(),
                Tables\Columns\TextColumn::make('quantity_used')->numeric(),
                Tables\Columns\TextColumn::make('unit_cost')->money('usd'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }
}
