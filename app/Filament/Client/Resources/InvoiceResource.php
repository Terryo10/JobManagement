<?php

namespace App\Filament\Client\Resources;

use App\Filament\Client\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';
    protected static ?string $navigationLabel = 'My Invoices';
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('client', fn ($q) => $q->where('email', auth()->user()?->email));
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('invoice_number')->sortable(),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) { 'draft' => 'gray', 'sent' => 'info', 'paid' => 'success', 'overdue' => 'danger', 'cancelled' => 'gray', default => 'gray' }),
            Tables\Columns\TextColumn::make('total')->money('USD')->sortable(),
            Tables\Columns\TextColumn::make('issued_at')->date()->sortable(),
            Tables\Columns\TextColumn::make('due_at')->date()->sortable(),
        ])
        ->filters([Tables\Filters\SelectFilter::make('status')->options(['sent' => 'Sent', 'paid' => 'Paid', 'overdue' => 'Overdue'])])
        ->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Invoice Details')->schema([
                Infolists\Components\TextEntry::make('invoice_number')->weight('bold'),
                Infolists\Components\TextEntry::make('status')->badge()->color(fn ($state) => match ($state) {
                    'draft' => 'gray', 'sent' => 'info', 'paid' => 'success',
                    'overdue' => 'danger', 'cancelled' => 'gray', default => 'gray',
                }),
                Infolists\Components\TextEntry::make('workOrder.reference_number')->label('Job Card')->placeholder('—'),
                Infolists\Components\TextEntry::make('currency'),
            ])->columns(4),
            Infolists\Components\Section::make('Financial Summary')->schema([
                Infolists\Components\TextEntry::make('subtotal')->money('usd'),
                Infolists\Components\TextEntry::make('tax_rate')->suffix('%'),
                Infolists\Components\TextEntry::make('tax_amount')->money('usd'),
                Infolists\Components\TextEntry::make('total')->money('usd')->weight('bold')->size('lg'),
            ])->columns(4),
            Infolists\Components\Section::make('Dates')->schema([
                Infolists\Components\TextEntry::make('issued_at')->label('Issue Date')->date(),
                Infolists\Components\TextEntry::make('due_at')->label('Due Date')->date()
                    ->color(fn ($record) => $record->due_at?->isPast() && $record->status !== 'paid' ? 'danger' : null),
                Infolists\Components\TextEntry::make('paid_at')->label('Paid At')->dateTime()->placeholder('Not yet paid'),
                Infolists\Components\TextEntry::make('payment_method')->placeholder('—'),
            ])->columns(4),
            Infolists\Components\Section::make('Notes')->schema([
                Infolists\Components\TextEntry::make('notes')->placeholder('No notes'),
            ])->collapsed(),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'view'  => Pages\ViewInvoice::route('/{record}'),
        ];
    }
}
