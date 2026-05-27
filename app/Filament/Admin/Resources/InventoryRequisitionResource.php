<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InventoryRequisitionResource\Pages;
use App\Models\InventoryRequisition;
use App\Models\Material;
use App\Services\InventoryService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InventoryRequisitionResource extends Resource
{
    protected static ?string $model = InventoryRequisition::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Requisitions';
    protected static ?string $modelLabel = 'Inventory Requisition';
    protected static ?string $pluralModelLabel = 'Inventory Requisitions';
    protected static ?string $navigationGroup = 'Warehouse';
    protected static ?int $navigationSort = 5;

    // ─────────────────────────────────────────────
    // Form
    // ─────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Request Details')
                ->schema([
                    Forms\Components\Select::make('type')
                        ->options([
                            'inventory'   => '📦 Draw from Stock',
                            'procurement' => '💰 Purchase Required',
                        ])
                        ->default('inventory')
                        ->required()
                        ->live()
                        ->label('Request Type')
                        ->helperText('Choose "Draw from Stock" if the item should already be available. Choose "Purchase Required" if we need to buy it first.'),

                    Forms\Components\Select::make('material_id')
                        ->label('Item / Material')
                        ->options(
                            Material::where('is_active', true)
                                ->get()
                                ->mapWithKeys(fn ($m) => [
                                    $m->id => "[{$m->sku}] {$m->name}" . ($m->stockLevel
                                        ? " — Stock: {$m->stockLevel->current_quantity} {$m->unit}"
                                        : " — No stock record"),
                                ])
                        )
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if (! $state) return;
                            $material = Material::with('stockLevel')->find($state);
                            if (! $material) return;
                            $stock = (float) ($material->stockLevel?->current_quantity ?? 0);
                            // Auto-suggest procurement if stock is 0
                            if ($stock <= 0) {
                                $set('type', 'procurement');
                                Notification::make()
                                    ->title('This item has no stock available.')
                                    ->body('The request type has been set to "Purchase Required". You can still change it if needed.')
                                    ->warning()
                                    ->send();
                            }
                        })
                        ->helperText('Current stock is shown next to each item.'),

                    Forms\Components\TextInput::make('quantity_requested')
                        ->label('Quantity Needed')
                        ->numeric()
                        ->required()
                        ->minValue(0.01)
                        ->step(0.01),

                    Forms\Components\Select::make('requested_by')
                        ->label('Requested By')
                        ->relationship('requestedBy', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->default(fn () => auth()->id()),

                    Forms\Components\Select::make('assigned_to')
                        ->label('Assign To (Recipient)')
                        ->relationship('assignedTo', 'name')
                        ->searchable()
                        ->preload()
                        ->helperText('Who will physically receive the item?'),

                    Forms\Components\Select::make('work_order_id')
                        ->label('Related Work Order (Optional)')
                        ->relationship('workOrder', 'reference_number')
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->reference_number} – {$record->title}")
                        ->searchable()
                        ->preload(),

                    // Procurement-only fields
                    Forms\Components\TextInput::make('estimated_cost')
                        ->label('Estimated Purchase Cost')
                        ->numeric()
                        ->prefix('$')
                        ->minValue(0)
                        ->visible(fn (Forms\Get $get) => $get('type') === 'procurement')
                        ->helperText('Approximate cost for budgeting.'),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notes / Justification')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    // ─────────────────────────────────────────────
    // Table
    // ─────────────────────────────────────────────

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
                    ->suffix(fn ($record) => ' ' . ($record->material?->unit ?? ''))
                    ->sortable(),

                Tables\Columns\TextColumn::make('quantity_issued')
                    ->label('Qty Issued')
                    ->suffix(fn ($record) => ' ' . ($record->material?->unit ?? ''))
                    ->placeholder('—'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => InventoryRequisition::statusLabel($state))
                    ->color(fn ($state) => InventoryRequisition::statusColor($state)),

                Tables\Columns\TextColumn::make('requestedBy.name')
                    ->label('Requested By')
                    ->searchable(),

                Tables\Columns\TextColumn::make('workOrder.reference_number')
                    ->label('Work Order')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('estimated_cost')
                    ->label('Est. Cost')
                    ->money('USD')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

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

                Tables\Filters\SelectFilter::make('material_id')
                    ->label('Item')
                    ->relationship('material', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                // ── Approve inventory draw ──────────────────────────────────
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Approve Requisition')
                    ->modalDescription('Approving this will allow the item to be issued from stock.')
                    ->visible(fn ($record) => $record->status === 'pending' && $record->type === 'inventory')
                    ->action(function ($record) {
                        try {
                            app(InventoryService::class)->approveInventoryRequisition($record, auth()->user());
                            Notification::make()->title('Requisition approved.')->success()->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
                        }
                    }),

                // ── Issue from stock (after approved) ───────────────────────
                Tables\Actions\Action::make('issue')
                    ->label('Issue Items')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Issue Items from Stock')
                    ->modalDescription(fn ($record) => "This will deduct {$record->quantity_requested} {$record->material?->unit} of \"{$record->material?->name}\" from the inventory ledger.")
                    ->visible(fn ($record) => $record->status === 'approved' && $record->type === 'inventory')
                    ->action(function ($record) {
                        try {
                            app(InventoryService::class)->issueFromStock($record, auth()->user());
                            Notification::make()->title('Items issued. Inventory ledger updated.')->success()->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()->title('Cannot Issue')->body($e->getMessage())->danger()->send();
                        }
                    }),

                // ── Approve procurement budget ──────────────────────────────
                Tables\Actions\Action::make('approve_money')
                    ->label('Approve Budget')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Approve Procurement Budget')
                    ->modalDescription('This approves the requested funds for purchasing the item.')
                    ->visible(fn ($record) => $record->status === 'pending' && $record->type === 'procurement')
                    ->action(function ($record) {
                        try {
                            app(InventoryService::class)->approveProcurementMoney($record, auth()->user());
                            Notification::make()->title('Budget approved.')->success()->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
                        }
                    }),

                // ── Mark money issued ────────────────────────────────────────
                Tables\Actions\Action::make('mark_money_issued')
                    ->label('Mark Funds Issued')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('primary')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Mark Funds as Issued')
                    ->modalDescription('Confirm that the approved funds have been handed to the requester.')
                    ->visible(fn ($record) => $record->status === 'money_approved')
                    ->action(function ($record) {
                        try {
                            app(InventoryService::class)->markMoneyIssued($record, auth()->user());
                            Notification::make()->title('Funds marked as issued.')->success()->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
                        }
                    }),

                // ── Confirm purchase ─────────────────────────────────────────
                Tables\Actions\Action::make('confirm_purchased')
                    ->label('Confirm Purchased')
                    ->icon('heroicon-o-shopping-bag')
                    ->color('info')
                    ->button()
                    ->visible(fn ($record) => $record->status === 'money_issued')
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
                            Notification::make()->title('Purchase confirmed. Ready to receive items.')->success()->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
                        }
                    }),

                // ── Confirm receipt & auto-issue ─────────────────────────────
                Tables\Actions\Action::make('confirm_received')
                    ->label('Confirm Received & Issue')
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->color('success')
                    ->button()
                    ->visible(fn ($record) => $record->status === 'items_purchased')
                    ->form([
                        Forms\Components\TextInput::make('quantity_received')
                            ->label('Quantity Actually Received')
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->helperText('Enter the quantity you actually received. This will be added to stock and auto-issued.'),
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

                // ── Reject ────────────────────────────────────────────────────
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

                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->visible(fn ($record) => $record->status === 'pending'),

                Tables\Actions\ViewAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ]);
    }

    // ─────────────────────────────────────────────
    // Infolist (View page)
    // ─────────────────────────────────────────────

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Requisition Summary')
                ->schema([
                    Infolists\Components\TextEntry::make('reference_number')->label('Reference')->weight('bold'),
                    Infolists\Components\TextEntry::make('type')
                        ->label('Type')
                        ->badge()
                        ->formatStateUsing(fn ($state) => $state === 'inventory' ? 'Stock Draw' : 'Procurement')
                        ->color(fn ($state) => $state === 'inventory' ? 'info' : 'warning'),
                    Infolists\Components\TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->formatStateUsing(fn ($state) => InventoryRequisition::statusLabel($state))
                        ->color(fn ($state) => InventoryRequisition::statusColor($state)),
                    Infolists\Components\TextEntry::make('material.name')
                        ->label('Item')
                        ->description(fn ($record) => $record->material?->sku),
                    Infolists\Components\TextEntry::make('quantity_requested')
                        ->label('Qty Requested')
                        ->suffix(fn ($record) => ' ' . ($record->material?->unit ?? '')),
                    Infolists\Components\TextEntry::make('quantity_issued')
                        ->label('Qty Issued')
                        ->suffix(fn ($record) => ' ' . ($record->material?->unit ?? ''))
                        ->placeholder('Not yet issued'),
                    Infolists\Components\TextEntry::make('requestedBy.name')->label('Requested By'),
                    Infolists\Components\TextEntry::make('assignedTo.name')->label('Recipient')->placeholder('—'),
                    Infolists\Components\TextEntry::make('workOrder.reference_number')->label('Work Order')->placeholder('—'),
                    Infolists\Components\TextEntry::make('estimated_cost')->label('Est. Cost')->money('USD')->placeholder('—'),
                ])
                ->columns(3),

            Infolists\Components\Section::make('Approval & Timeline')
                ->schema([
                    Infolists\Components\TextEntry::make('approvedBy.name')->label('Approved / Actioned By')->placeholder('—'),
                    Infolists\Components\TextEntry::make('approved_at')->label('Actioned At')->dateTime()->placeholder('—'),
                    Infolists\Components\TextEntry::make('issued_at')->label('Issued At')->dateTime()->placeholder('—'),
                    Infolists\Components\TextEntry::make('created_at')->label('Submitted At')->dateTime(),
                    Infolists\Components\TextEntry::make('notes')->label('Notes')->columnSpanFull()->placeholder('—'),
                    Infolists\Components\TextEntry::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->columnSpanFull()
                        ->placeholder('—')
                        ->visible(fn ($record) => $record->status === 'rejected'),
                ])
                ->columns(2),

            Infolists\Components\Section::make('Procurement Chain')
                ->schema([
                    Infolists\Components\TextEntry::make('procurementRequisition.reference_number')
                        ->label('Funded By (Procurement Ref)')
                        ->placeholder('—'),
                    Infolists\Components\TextEntry::make('originatingRequisition.reference_number')
                        ->label('Original Inventory Request')
                        ->placeholder('—'),
                    Infolists\Components\TextEntry::make('purchaseOrder.reference_number')
                        ->label('Purchase Order')
                        ->placeholder('—'),
                ])
                ->columns(3)
                ->visible(fn ($record) => $record->type === 'procurement' || $record->procurement_requisition_id),
        ]);
    }

    // ─────────────────────────────────────────────
    // Pages
    // ─────────────────────────────────────────────

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInventoryRequisitions::route('/'),
            'create' => Pages\CreateInventoryRequisition::route('/create'),
            'edit'   => Pages\EditInventoryRequisition::route('/{record}/edit'),
            'view'   => Pages\ViewInventoryRequisition::route('/{record}'),
        ];
    }
}
