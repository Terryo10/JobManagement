<?php

namespace App\Filament\Accountant\Resources;

use App\Filament\Accountant\Resources\InventoryRequisitionResource\Pages;
use App\Models\InventoryRequisition;
use App\Services\InventoryService;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Accountant panel view of inventory requisitions.
 * Accountants can approve/reject procurement budgets and issue funds.
 * They can also approve and issue inventory-type requisitions.
 */
class InventoryRequisitionResource extends Resource
{
    protected static ?string $model = InventoryRequisition::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Inventory Requisitions';
    protected static ?string $modelLabel = 'Inventory Requisition';
    protected static ?string $pluralModelLabel = 'Inventory Requisitions';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 5;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Reference')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => $state === 'inventory' ? 'Stock Draw' : 'Procurement')
                    ->color(fn ($state) => $state === 'inventory' ? 'info' : 'warning'),

                Tables\Columns\TextColumn::make('material.name')
                    ->label('Item')
                    ->searchable()
                    ->description(fn ($record) => $record->material?->sku),

                Tables\Columns\TextColumn::make('quantity_requested')
                    ->label('Qty Requested')
                    ->suffix(fn ($record) => ' ' . ($record->material?->unit ?? '')),

                Tables\Columns\TextColumn::make('estimated_cost')
                    ->label('Est. Cost')
                    ->money('USD')
                    ->placeholder('—'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => InventoryRequisition::statusLabel($state))
                    ->color(fn ($state) => InventoryRequisition::statusColor($state)),

                Tables\Columns\TextColumn::make('requestedBy.name')
                    ->label('Requested By')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect([
                        'pending', 'approved', 'rejected', 'money_approved',
                        'money_issued', 'items_purchased', 'items_received', 'issued',
                    ])->mapWithKeys(fn ($s) => [$s => InventoryRequisition::statusLabel($s)])),

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'inventory'   => 'Stock Draw',
                        'procurement' => 'Procurement',
                    ]),
            ])
            ->actions([
                // Inventory type — approve
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Approve Requisition')
                    ->visible(fn ($record) => $record->status === 'pending' && $record->type === 'inventory')
                    ->action(function ($record) {
                        try {
                            app(InventoryService::class)->approveInventoryRequisition($record, auth()->user());
                            Notification::make()->title('Requisition approved.')->success()->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
                        }
                    }),

                // Inventory type — issue
                Tables\Actions\Action::make('issue')
                    ->label('Issue Items')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Issue Items from Stock')
                    ->modalDescription(fn ($record) => "Deducts {$record->quantity_requested} {$record->material?->unit} of \"{$record->material?->name}\" from inventory.")
                    ->visible(fn ($record) => $record->status === 'approved' && $record->type === 'inventory')
                    ->action(function ($record) {
                        try {
                            app(InventoryService::class)->issueFromStock($record, auth()->user());
                            Notification::make()->title('Items issued. Inventory ledger updated.')->success()->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()->title('Cannot Issue')->body($e->getMessage())->danger()->send();
                        }
                    }),

                // Procurement — approve budget
                Tables\Actions\Action::make('approve_money')
                    ->label('Approve Budget')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Approve Procurement Budget')
                    ->modalDescription('Approves the requested funds for purchasing this item.')
                    ->visible(fn ($record) => $record->status === 'pending' && $record->type === 'procurement')
                    ->action(function ($record) {
                        try {
                            app(InventoryService::class)->approveProcurementMoney($record, auth()->user());
                            Notification::make()->title('Budget approved.')->success()->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
                        }
                    }),

                // Procurement — mark funds issued
                Tables\Actions\Action::make('mark_money_issued')
                    ->label('Mark Funds Issued')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('primary')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Mark Funds as Issued')
                    ->visible(fn ($record) => $record->status === 'money_approved')
                    ->action(function ($record) {
                        try {
                            app(InventoryService::class)->markMoneyIssued($record, auth()->user());
                            Notification::make()->title('Funds marked as issued.')->success()->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
                        }
                    }),

                // Reject
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->button()
                    ->visible(fn ($record) => in_array($record->status, ['pending', 'approved', 'money_approved']))
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Reason for Rejection')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        try {
                            app(InventoryService::class)->reject($record, auth()->user(), $data['rejection_reason']);
                            Notification::make()->title('Requisition rejected.')->warning()->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
                        }
                    }),

                // Procurement — confirm purchased
                Tables\Actions\Action::make('confirm_purchased')
                    ->label('Confirm Purchased')
                    ->icon('heroicon-o-shopping-bag')
                    ->color('info')
                    ->button()
                    ->visible(fn ($record) => $record->status === 'money_issued' && $record->type === 'procurement')
                    ->modalHeading('Confirm Purchase')
                    ->modalDescription('Confirm that the funds were used and the item has been purchased.')
                    ->form([
                        Forms\Components\Select::make('purchase_order_id')
                            ->label('Link to Purchase Order (Optional)')
                            ->relationship('purchaseOrder', 'reference_number')
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function ($record, array $data) {
                        try {
                            app(InventoryService::class)->confirmPurchased(
                                $record,
                                auth()->user(),
                                $data['purchase_order_id'] ?? null,
                            );
                            Notification::make()->title('Purchase confirmed. Ready to receive items into stock.')->success()->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
                        }
                    }),

                // Procurement — confirm receipt & auto-issue
                Tables\Actions\Action::make('confirm_received')
                    ->label('Confirm Received & Issue')
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->color('success')
                    ->button()
                    ->visible(fn ($record) => $record->status === 'items_purchased' && $record->type === 'procurement')
                    ->modalHeading('Confirm Items Received')
                    ->form([
                        Forms\Components\TextInput::make('quantity_received')
                            ->label('Quantity Actually Received')
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->helperText('This quantity will be added to stock and the original request auto-issued.'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Receipt Notes')
                            ->rows(2),
                    ])
                    ->action(function ($record, array $data) {
                        try {
                            app(InventoryService::class)->confirmReceiptAndAutoIssue(
                                procurementReq:   $record,
                                receivedBy:       auth()->user(),
                                quantityReceived: (float) $data['quantity_received'],
                                notes:            $data['notes'] ?? null,
                            );
                            Notification::make()
                                ->title('Items received, stock updated, and original request auto-issued.')
                                ->success()
                                ->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
                        }
                    }),

                Tables\Actions\ViewAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Requisition Summary')
                ->schema([
                    Infolists\Components\TextEntry::make('reference_number')->label('Reference')->weight('bold'),
                    Infolists\Components\TextEntry::make('type')
                        ->label('Type')->badge()
                        ->formatStateUsing(fn ($state) => $state === 'inventory' ? 'Stock Draw' : 'Procurement')
                        ->color(fn ($state) => $state === 'inventory' ? 'info' : 'warning'),
                    Infolists\Components\TextEntry::make('status')
                        ->label('Status')->badge()
                        ->formatStateUsing(fn ($state) => InventoryRequisition::statusLabel($state))
                        ->color(fn ($state) => InventoryRequisition::statusColor($state)),
                    Infolists\Components\TextEntry::make('material.name')->label('Item')
                        ->description(fn ($record) => $record->material?->sku),
                    Infolists\Components\TextEntry::make('quantity_requested')
                        ->label('Qty Requested')
                        ->suffix(fn ($record) => ' ' . ($record->material?->unit ?? '')),
                    Infolists\Components\TextEntry::make('estimated_cost')->label('Est. Cost')->money('USD')->placeholder('—'),
                    Infolists\Components\TextEntry::make('requestedBy.name')->label('Requested By'),
                    Infolists\Components\TextEntry::make('notes')->label('Notes')->columnSpanFull()->placeholder('—'),
                    Infolists\Components\TextEntry::make('rejection_reason')
                        ->label('Rejection Reason')->columnSpanFull()->placeholder('—')
                        ->visible(fn ($record) => $record->status === 'rejected'),
                ])
                ->columns(3),
        ]);
    }

    public static function canCreate(): bool
    {
        return false; // creation via Admin or Staff panels only
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryRequisitions::route('/'),
            'view'  => Pages\ViewInventoryRequisition::route('/{record}'),
        ];
    }
}
