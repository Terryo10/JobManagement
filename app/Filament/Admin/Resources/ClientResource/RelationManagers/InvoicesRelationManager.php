<?php

namespace App\Filament\Admin\Resources\ClientResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';
    protected static ?string $title = 'Invoices';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('workOrder.title')->limit(30)->label('Job'),
                Tables\Columns\TextColumn::make('total')->money('usd')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'draft' => 'gray', 'sent' => 'info', 'paid' => 'success',
                    'overdue' => 'danger', 'cancelled' => 'warning', default => 'gray',
                }),
                Tables\Columns\TextColumn::make('due_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('issued_at')->date()->label('Issued'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.admin.resources.invoices.edit', $record)),
            ]);
    }
}
