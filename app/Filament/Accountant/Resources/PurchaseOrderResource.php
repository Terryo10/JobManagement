<?php

namespace App\Filament\Accountant\Resources;

use App\Filament\Accountant\Resources\PurchaseOrderResource\Pages;
use App\Models\PurchaseOrder;
use Filament\Forms;
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
    protected static ?string $navigationLabel = 'Purchase Orders';
    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('reference_number')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('supplier.name')->label('Supplier')->sortable(),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                'draft' => 'gray', 'submitted' => 'info', 'approved' => 'success',
                'ordered' => 'warning', 'delivered' => 'success', 'cancelled' => 'danger', default => 'gray',
            }),
            Tables\Columns\TextColumn::make('total_amount')->money('USD')->sortable(),
            Tables\Columns\TextColumn::make('orderedBy.name')->label('Ordered By'),
            Tables\Columns\TextColumn::make('expected_delivery')->date()->sortable(),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')->options([
                'submitted' => 'Submitted', 'approved' => 'Approved', 'ordered' => 'Ordered', 'delivered' => 'Delivered',
            ]),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn ($record) => $record->status === 'submitted')
                ->action(function ($record) {
                    $record->update(['status' => 'approved', 'approved_by' => auth()->id()]);
                    Notification::make()->title('Purchase order approved.')->success()->send();
                }),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Purchase Order Details')->schema([
                Infolists\Components\TextEntry::make('reference_number')->weight('bold'),
                Infolists\Components\TextEntry::make('supplier.name'),
                Infolists\Components\TextEntry::make('status')->badge(),
                Infolists\Components\TextEntry::make('total_amount')->money('USD'),
                Infolists\Components\TextEntry::make('orderedBy.name'),
                Infolists\Components\TextEntry::make('approvedBy.name')->placeholder('—'),
                Infolists\Components\TextEntry::make('expected_delivery')->date(),
                Infolists\Components\TextEntry::make('delivered_at')->dateTime()->placeholder('—'),
                Infolists\Components\TextEntry::make('notes')->columnSpanFull(),
            ])->columns(3),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseOrders::route('/'),
            'view'  => Pages\ViewPurchaseOrder::route('/{record}'),
        ];
    }
}
