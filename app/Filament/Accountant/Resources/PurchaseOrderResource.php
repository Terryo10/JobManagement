<?php

namespace App\Filament\Accountant\Resources;

use App\Filament\Accountant\Resources\PurchaseOrderResource\Pages;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Purchase Orders';
    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canDelete($record): bool
    {
        return true;
    }

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
            Tables\Actions\Action::make('downloadPdf')
                ->label('Download Requisition')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(function ($record) {
                    $record->load('items', 'supplier', 'orderedBy', 'approvedBy');
                    $pdf = Pdf::loadView('pdf.payment-requisition', ['purchaseOrder' => $record]);
                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        "requisition-{$record->reference_number}.pdf"
                    );
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
        return [
            \App\Filament\Accountant\Resources\PurchaseOrderResource\RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'view'  => Pages\ViewPurchaseOrder::route('/{record}'),
            'edit'  => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
