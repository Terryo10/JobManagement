<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\InventoryRequisitionResource\Pages;
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

/**
 * Staff-facing resource for submitting and tracking inventory/tool requests.
 * Scoped to the current user's own submissions.
 */
class InventoryRequisitionResource extends Resource
{
    protected static ?string $model = InventoryRequisition::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'My Requests';
    protected static ?string $modelLabel = 'Item Request';
    protected static ?string $pluralModelLabel = 'My Item Requests';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?int $navigationSort = 10;

    /**
     * Staff only see their own requisitions.
     */
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('requested_by', auth()->id());
    }

    // ─────────────────────────────────────────────
    // Form — kept simple for non-technical users
    // ─────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Submit an Item or Tool Request')
                ->description('Fill in what you need below. We\'ll let you know once it\'s been reviewed.')
                ->schema([
                    Forms\Components\Select::make('material_id')
                        ->label('What do you need?')
                        ->options(
                            Material::where('is_active', true)
                                ->get()
                                ->mapWithKeys(fn ($m) => [
                                    $m->id => "{$m->name}" . ($m->stockLevel
                                        ? " (In stock: {$m->stockLevel->current_quantity} {$m->unit})"
                                        : " (Stock unknown)"),
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
                            if ($stock <= 0) {
                                $set('type', 'procurement');
                                Notification::make()
                                    ->title('This item is currently out of stock.')
                                    ->body('Your request has been set to "Purchase Required". Approval will include a budget sign-off before the item is bought.')
                                    ->warning()
                                    ->send();
                            } else {
                                $set('type', 'inventory');
                            }
                        })
                        ->helperText('Can\'t find what you need? Ask your manager to add it to the catalogue first.'),

                    Forms\Components\TextInput::make('quantity_requested')
                        ->label('How many / how much?')
                        ->numeric()
                        ->required()
                        ->minValue(0.01)
                        ->step(0.01)
                        ->helperText('Enter the quantity you need.'),

                    // Hidden — auto-set by afterStateUpdated on material_id
                    Forms\Components\Select::make('type')
                        ->label('Request Type')
                        ->options([
                            'inventory'   => '📦 Draw from Stock (item is available)',
                            'procurement' => '💰 Purchase Required (needs to be bought)',
                        ])
                        ->default('inventory')
                        ->required()
                        ->live()
                        ->helperText('This is set automatically based on stock, but you can override it.'),

                    Forms\Components\Select::make('work_order_id')
                        ->label('Which job is this for? (Optional)')
                        ->relationship('workOrder', 'reference_number')
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->reference_number} – {$record->title}")
                        ->searchable()
                        ->preload()
                        ->helperText('Link to a job/work order if applicable.'),

                    // Estimated cost — shown only when procurement
                    Forms\Components\TextInput::make('estimated_cost')
                        ->label('Estimated Cost ($)')
                        ->numeric()
                        ->prefix('$')
                        ->minValue(0)
                        ->visible(fn (Forms\Get $get) => $get('type') === 'procurement')
                        ->helperText('Your best estimate of what this will cost to purchase.'),

                    Forms\Components\Textarea::make('notes')
                        ->label('Reason / Additional Notes')
                        ->rows(3)
                        ->columnSpanFull()
                        ->helperText('Why do you need this? Any specific requirements?'),
                ])
                ->columns(2),
        ]);
    }

    // ─────────────────────────────────────────────
    // Table — clear status tracking for staff
    // ─────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Ref #')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('material.name')
                    ->label('Item Requested')
                    ->searchable(),

                Tables\Columns\TextColumn::make('quantity_requested')
                    ->label('Qty')
                    ->suffix(fn ($record) => ' ' . ($record->material?->unit ?? '')),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => $state === 'inventory' ? 'From Stock' : 'Purchase')
                    ->color(fn ($state) => $state === 'inventory' ? 'info' : 'warning'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => InventoryRequisition::statusLabel($state))
                    ->color(fn ($state) => InventoryRequisition::statusColor($state)),

                Tables\Columns\TextColumn::make('issued_at')
                    ->label('Issued At')
                    ->dateTime()
                    ->placeholder('Pending')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect([
                        'pending', 'approved', 'rejected', 'money_approved',
                        'money_issued', 'items_purchased', 'items_received', 'issued',
                    ])->mapWithKeys(fn ($s) => [$s => InventoryRequisition::statusLabel($s)])),
            ])
            ->actions([
                // ── Confirm purchased (staff bought the item with issued funds) ───
                Tables\Actions\Action::make('confirm_purchased')
                    ->label('I Bought It')
                    ->icon('heroicon-o-shopping-bag')
                    ->color('info')
                    ->button()
                    ->visible(fn ($record) => $record->status === 'money_issued' && $record->type === 'procurement')
                    ->modalHeading('Confirm Purchase')
                    ->modalDescription('Confirm that you have purchased the item with the issued funds. The next step will be to confirm you have physically received it.')
                    ->form([
                        Forms\Components\Select::make('purchase_order_id')
                            ->label('Link to Purchase Order (Optional)')
                            ->relationship('purchaseOrder', 'reference_number')
                            ->searchable()
                            ->preload()
                            ->helperText('If a purchase order was raised, link it here.'),
                    ])
                    ->action(function ($record, array $data) {
                        try {
                            app(InventoryService::class)->confirmPurchased(
                                $record,
                                auth()->user(),
                                $data['purchase_order_id'] ?? null,
                            );
                            Notification::make()
                                ->title('Purchase confirmed!')
                                ->body('An admin will now receive and log the items into stock.')
                                ->success()
                                ->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
                        }
                    }),

                // ── Confirm received & auto-issue ────────────────────────────────
                Tables\Actions\Action::make('confirm_received')
                    ->label('Items Received')
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->color('success')
                    ->button()
                    ->visible(fn ($record) => $record->status === 'items_purchased' && $record->type === 'procurement')
                    ->modalHeading('Confirm Items Received')
                    ->form([
                        Forms\Components\TextInput::make('quantity_received')
                            ->label('How many did you receive?')
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->helperText('Enter the actual quantity you brought back. This will be added to stock and your original request will be fulfilled automatically.'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Any notes? (Optional)')
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
                                ->title('Done! Items logged and your request has been fulfilled.')
                                ->success()
                                ->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
                        }
                    }),

                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->visible(fn ($record) => $record->status === 'pending'),

                Tables\Actions\ViewAction::make()->iconButton(),
            ])
            ->bulkActions([]);
    }

    // ─────────────────────────────────────────────
    // Infolist — clear, plain-English status view
    // ─────────────────────────────────────────────

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Your Request')
                ->schema([
                    Infolists\Components\TextEntry::make('reference_number')->label('Request ID')->weight('bold'),
                    Infolists\Components\TextEntry::make('status')
                        ->label('Current Status')
                        ->badge()
                        ->formatStateUsing(fn ($state) => InventoryRequisition::statusLabel($state))
                        ->color(fn ($state) => InventoryRequisition::statusColor($state)),
                    Infolists\Components\TextEntry::make('material.name')->label('Item'),
                    Infolists\Components\TextEntry::make('quantity_requested')
                        ->label('Quantity Requested')
                        ->suffix(fn ($record) => ' ' . ($record->material?->unit ?? '')),
                    Infolists\Components\TextEntry::make('quantity_issued')
                        ->label('Quantity Issued')
                        ->suffix(fn ($record) => ' ' . ($record->material?->unit ?? ''))
                        ->placeholder('Not yet issued'),
                    Infolists\Components\TextEntry::make('type')
                        ->label('Request Type')
                        ->badge()
                        ->formatStateUsing(fn ($state) => $state === 'inventory' ? 'From Stock' : 'Purchase Required')
                        ->color(fn ($state) => $state === 'inventory' ? 'info' : 'warning'),
                    Infolists\Components\TextEntry::make('estimated_cost')
                        ->label('Estimated Cost')
                        ->money('USD')
                        ->placeholder('—')
                        ->visible(fn ($record) => $record->type === 'procurement'),
                    Infolists\Components\TextEntry::make('workOrder.reference_number')->label('Work Order')->placeholder('—'),
                    Infolists\Components\TextEntry::make('notes')->label('Your Notes')->columnSpanFull()->placeholder('—'),
                    Infolists\Components\TextEntry::make('rejection_reason')
                        ->label('Reason for Rejection')
                        ->columnSpanFull()
                        ->placeholder('—')
                        ->visible(fn ($record) => $record->status === 'rejected'),
                ])
                ->columns(3),

            Infolists\Components\Section::make('Timeline')
                ->schema([
                    Infolists\Components\TextEntry::make('created_at')->label('Submitted At')->dateTime(),
                    Infolists\Components\TextEntry::make('approved_at')->label('Reviewed At')->dateTime()->placeholder('—'),
                    Infolists\Components\TextEntry::make('issued_at')->label('Issued At')->dateTime()->placeholder('—'),
                ])
                ->columns(3),
        ]);
    }

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
