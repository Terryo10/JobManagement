<?php

namespace App\Filament\Accountant\Resources\PurchaseOrderResource\RelationManagers;

use App\Models\Material;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Order Items';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('material_id')
                ->label('Material')
                ->options(Material::where('is_active', true)->pluck('name', 'id'))
                ->searchable()->required()
                ->reactive()
                ->afterStateUpdated(function (Set $set, $state) {
                    if ($state) {
                        $material = Material::find($state);
                        if ($material) {
                            $set('unit_price', $material->unit_cost);
                            $set('unit', $material->unit);
                        }
                    }
                }),
            Forms\Components\TextInput::make('quantity')->numeric()->required()->default(1)
                ->reactive()
                ->afterStateUpdated(fn (Set $set, Get $get) => $set('total_price', round(($get('quantity') ?: 0) * ($get('unit_price') ?: 0), 2))),
            Forms\Components\TextInput::make('unit')->maxLength(50),
            Forms\Components\TextInput::make('unit_price')->numeric()->required()->prefix('$')
                ->reactive()
                ->afterStateUpdated(fn (Set $set, Get $get) => $set('total_price', round(($get('quantity') ?: 0) * ($get('unit_price') ?: 0), 2))),
            Forms\Components\TextInput::make('total_price')->numeric()->prefix('$')->disabled()->dehydrated(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('material.name')->label('Material'),
                Tables\Columns\TextColumn::make('quantity')->numeric(2),
                Tables\Columns\TextColumn::make('unit'),
                Tables\Columns\TextColumn::make('unit_price')->money('usd'),
                Tables\Columns\TextColumn::make('total_price')->money('usd'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(fn () => $this->recalculatePO()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(fn () => $this->recalculatePO()),
                Tables\Actions\DeleteAction::make()
                    ->after(fn () => $this->recalculatePO()),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()
                    ->after(fn () => $this->recalculatePO()),
            ])]);
    }

    private function recalculatePO(): void
    {
        $po = $this->getOwnerRecord();
        $po->update(['total_amount' => $po->items()->sum('total_price')]);
    }
}
