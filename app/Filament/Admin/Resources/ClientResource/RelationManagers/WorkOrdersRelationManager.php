<?php

namespace App\Filament\Admin\Resources\ClientResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class WorkOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'workOrders';
    protected static ?string $title = 'Work Orders';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('title')->limit(35),
                Tables\Columns\TextColumn::make('category')->badge(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'pending' => 'gray', 'in_progress' => 'warning', 'on_hold' => 'info',
                    'completed' => 'success', 'cancelled' => 'danger', default => 'gray',
                }),
                Tables\Columns\TextColumn::make('priority')->badge()->color(fn ($state) => match ($state) {
                    'low' => 'gray', 'normal' => 'info', 'high' => 'warning', 'urgent' => 'danger', default => 'gray',
                }),
                Tables\Columns\TextColumn::make('deadline')->date()->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.admin.resources.work-orders.edit', $record)),
            ]);
    }
}
