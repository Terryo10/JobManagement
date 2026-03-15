<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PurchaseOrderResource\Pages;
use App\Filament\Admin\Resources\PurchaseOrderResource\RelationManagers;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Warehouse';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('reference_number')->required()->maxLength(50)
                    ->unique(ignoreRecord: true)
                    ->default(fn () => 'PO-' . now()->format('Y') . '-' . str_pad(PurchaseOrder::count() + 1, 4, '0', STR_PAD_LEFT)),
                Forms\Components\Select::make('supplier_id')
                    ->relationship('supplier', 'name')->searchable()->preload()->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved',
                        'ordered' => 'Ordered', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled',
                    ])
                    ->default('draft')->required(),
                Forms\Components\Select::make('ordered_by')
                    ->relationship('orderedBy', 'name')->searchable()->preload()
                    ->default(fn () => auth()->id()),
                Forms\Components\Select::make('approved_by')
                    ->relationship('approvedBy', 'name')->searchable()->preload(),
                Forms\Components\TextInput::make('total_amount')->numeric()->prefix('$')->default(0)
                    ->disabled()->dehydrated(),
                Forms\Components\DatePicker::make('expected_delivery'),
                Forms\Components\DateTimePicker::make('delivered_at'),
                Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('reference_number')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('supplier.name')->sortable(),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                'draft' => 'gray', 'submitted' => 'info', 'approved' => 'warning',
                'ordered' => 'primary', 'delivered' => 'success', 'cancelled' => 'danger',
                default => 'gray',
            }),
            Tables\Columns\TextColumn::make('total_amount')->money('USD')->sortable(),
            Tables\Columns\TextColumn::make('orderedBy.name')->label('Ordered By'),
            Tables\Columns\TextColumn::make('expected_delivery')->date()->sortable(),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')->options([
                'draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved',
                'ordered' => 'Ordered', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled',
            ]),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('submit')
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->requiresConfirmation()
                ->visible(fn ($record) => $record->status === 'draft')
                ->action(fn ($record) => $record->update(['status' => 'submitted'])),
            Tables\Actions\Action::make('approve')
                ->icon('heroicon-o-check-circle')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn ($record) => $record->status === 'submitted')
                ->action(fn ($record) => $record->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                ])),
            Tables\Actions\Action::make('order')
                ->icon('heroicon-o-truck')
                ->color('primary')
                ->requiresConfirmation()
                ->visible(fn ($record) => $record->status === 'approved')
                ->action(fn ($record) => $record->update(['status' => 'ordered'])),
            Tables\Actions\Action::make('deliver')
                ->icon('heroicon-o-archive-box-arrow-down')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn ($record) => $record->status === 'ordered')
                ->action(function ($record) {
                    $record->update([
                        'status' => 'delivered',
                        'delivered_at' => now(),
                    ]);

                    // Auto-update stock levels
                    foreach ($record->items as $item) {
                        if ($item->material_id) {
                            $stock = \App\Models\StockLevel::firstOrCreate(
                                ['material_id' => $item->material_id],
                                ['current_quantity' => 0, 'last_updated' => now(), 'last_updated_by' => auth()->id()]
                            );
                            $stock->update([
                                'current_quantity' => $stock->current_quantity + $item->quantity,
                                'last_updated' => now(),
                                'last_updated_by' => auth()->id(),
                            ]);
                        }
                    }

                    Notification::make()
                        ->title('Delivery Received')
                        ->body("PO {$record->reference_number} delivered and stock updated")
                        ->success()
                        ->send();
                }),
        ])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Purchase Order Details')->schema([
                Infolists\Components\TextEntry::make('reference_number'),
                Infolists\Components\TextEntry::make('supplier.name'),
                Infolists\Components\TextEntry::make('status')->badge(),
                Infolists\Components\TextEntry::make('total_amount')->money('usd'),
                Infolists\Components\TextEntry::make('orderedBy.name'),
                Infolists\Components\TextEntry::make('approvedBy.name'),
                Infolists\Components\TextEntry::make('expected_delivery')->date(),
                Infolists\Components\TextEntry::make('delivered_at')->dateTime(),
                Infolists\Components\TextEntry::make('notes')->columnSpanFull(),
            ])->columns(3),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
            \App\Filament\Admin\Resources\PurchaseOrderResource\RelationManagers\DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'view'   => Pages\ViewPurchaseOrder::route('/{record}'),
            'edit'   => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
