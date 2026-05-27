<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InventoryTransactionResource\Pages;
use App\Models\InventoryTransaction;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Read-only inventory ledger — no create/edit/delete.
 * Entries are written exclusively by InventoryService via StockLevel helpers.
 */
class InventoryTransactionResource extends Resource
{
    protected static ?string $model = InventoryTransaction::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Inventory Ledger';
    protected static ?string $modelLabel = 'Inventory Transaction';
    protected static ?string $pluralModelLabel = 'Inventory Ledger';
    protected static ?string $navigationGroup = 'Warehouse';
    protected static ?int $navigationSort = 6;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date / Time')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('material.name')
                    ->label('Item')
                    ->searchable()
                    ->description(fn ($record) => $record->material?->sku),

                Tables\Columns\BadgeColumn::make('transaction_type')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => InventoryTransaction::typeLabel($state))
                    ->color(fn ($state) => InventoryTransaction::typeColor($state)),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantity')
                    ->suffix(fn ($record) => ' ' . ($record->material?->unit ?? ''))
                    ->sortable(),

                Tables\Columns\TextColumn::make('balance_before')
                    ->label('Balance Before')
                    ->suffix(fn ($record) => ' ' . ($record->material?->unit ?? ''))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('balance_after')
                    ->label('Balance After')
                    ->suffix(fn ($record) => ' ' . ($record->material?->unit ?? ''))
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('performedBy.name')
                    ->label('Performed By')
                    ->searchable(),

                Tables\Columns\TextColumn::make('reference_type')
                    ->label('Source')
                    ->formatStateUsing(fn ($state) => $state ? class_basename($state) : '—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(50)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('transaction_type')
                    ->label('Type')
                    ->options([
                        'addition'   => 'Stock In',
                        'deduction'  => 'Stock Out',
                        'adjustment' => 'Adjustment',
                    ]),

                Tables\Filters\SelectFilter::make('material_id')
                    ->label('Item')
                    ->relationship('material', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('created_at')
                    ->label('Date Range')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('From'),
                        \Filament\Forms\Components\DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'],  fn ($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('created_at', '<=', $data['until']));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton(),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Transaction Detail')
                ->schema([
                    Infolists\Components\TextEntry::make('created_at')->label('Date / Time')->dateTime(),
                    Infolists\Components\TextEntry::make('material.name')->label('Item')
                        ->description(fn ($record) => $record->material?->sku),
                    Infolists\Components\TextEntry::make('transaction_type')
                        ->label('Type')
                        ->badge()
                        ->formatStateUsing(fn ($state) => InventoryTransaction::typeLabel($state))
                        ->color(fn ($state) => InventoryTransaction::typeColor($state)),
                    Infolists\Components\TextEntry::make('quantity')
                        ->label('Quantity')
                        ->suffix(fn ($record) => ' ' . ($record->material?->unit ?? '')),
                    Infolists\Components\TextEntry::make('balance_before')
                        ->label('Balance Before')
                        ->suffix(fn ($record) => ' ' . ($record->material?->unit ?? '')),
                    Infolists\Components\TextEntry::make('balance_after')
                        ->label('Balance After')
                        ->suffix(fn ($record) => ' ' . ($record->material?->unit ?? ''))
                        ->weight('bold'),
                    Infolists\Components\TextEntry::make('performedBy.name')->label('Performed By'),
                    Infolists\Components\TextEntry::make('reference_type')
                        ->label('Source Type')
                        ->formatStateUsing(fn ($state) => $state ? class_basename($state) : '—'),
                    Infolists\Components\TextEntry::make('reference_id')->label('Source ID')->placeholder('—'),
                    Infolists\Components\TextEntry::make('notes')->label('Notes')->columnSpanFull()->placeholder('—'),
                ])
                ->columns(3),
        ]);
    }

    public static function canCreate(): bool
    {
        return false; // append-only — created by service only
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryTransactions::route('/'),
        ];
    }
}
