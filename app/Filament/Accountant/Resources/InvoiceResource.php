<?php

namespace App\Filament\Accountant\Resources;

use App\Filament\Accountant\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';
    protected static ?string $navigationLabel = 'Invoices';
    protected static ?int $navigationSort = 1;

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
            Forms\Components\Tabs::make('Invoice')->tabs([
                Forms\Components\Tabs\Tab::make('Details')->icon('heroicon-o-information-circle')->schema([
                    Forms\Components\TextInput::make('invoice_number')->required()->maxLength(50)
                        ->unique(ignoreRecord: true)
                        ->default(fn () => 'INV-' . now()->format('Y') . '-' . str_pad(Invoice::count() + 1, 4, '0', STR_PAD_LEFT)),
                    Forms\Components\Select::make('client_id')
                        ->relationship('client', 'company_name')->searchable()->preload()->required(),
                    Forms\Components\Select::make('work_order_id')
                        ->relationship('workOrder', 'reference_number')->searchable()->preload()
                        ->helperText('Link to a specific job card'),
                    Forms\Components\Select::make('status')
                        ->options(['draft' => 'Draft', 'sent' => 'Sent', 'paid' => 'Paid', 'overdue' => 'Overdue', 'cancelled' => 'Cancelled'])
                        ->default('draft')->required(),
                    Forms\Components\TextInput::make('currency')->default('USD')->maxLength(10),
                    Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
                ])->columns(2),
                Forms\Components\Tabs\Tab::make('Financials')->icon('heroicon-o-calculator')->schema([
                    Forms\Components\TextInput::make('subtotal')->numeric()->prefix('$')->default(0)->disabled()->dehydrated(),
                    Forms\Components\TextInput::make('tax_rate')->numeric()->suffix('%')->default(0)
                        ->reactive()
                        ->afterStateUpdated(function (\Filament\Forms\Set $set, \Filament\Forms\Get $get) {
                            $subtotal = (float) ($get('subtotal') ?: 0);
                            $taxRate = (float) ($get('tax_rate') ?: 0);
                            $taxAmount = round($subtotal * ($taxRate / 100), 2);
                            $set('tax_amount', $taxAmount);
                            $set('total', $subtotal + $taxAmount);
                        }),
                    Forms\Components\TextInput::make('tax_amount')->numeric()->prefix('$')->default(0)->disabled()->dehydrated(),
                    Forms\Components\TextInput::make('total')->numeric()->prefix('$')->default(0)->disabled()->dehydrated(),
                ])->columns(2),
                Forms\Components\Tabs\Tab::make('Dates & Payment')->icon('heroicon-o-calendar')->schema([
                    Forms\Components\DatePicker::make('issued_at')->label('Issue Date'),
                    Forms\Components\DatePicker::make('due_at')->label('Due Date'),
                    Forms\Components\DateTimePicker::make('paid_at')->label('Paid At'),
                    Forms\Components\TextInput::make('payment_method')->maxLength(100),
                    Forms\Components\TextInput::make('payment_reference')->maxLength(255),
                ])->columns(2),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('invoice_number')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('client.company_name')->label('Client')->sortable()->limit(25),
            Tables\Columns\TextColumn::make('workOrder.reference_number')->label('Job Card')->placeholder('—'),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                'draft' => 'gray', 'sent' => 'info', 'paid' => 'success',
                'overdue' => 'danger', 'cancelled' => 'gray', default => 'gray',
            }),
            Tables\Columns\TextColumn::make('total')->money('USD')->sortable(),
            Tables\Columns\TextColumn::make('issued_at')->date()->sortable(),
            Tables\Columns\TextColumn::make('due_at')->date()->sortable()
                ->color(fn ($record) => $record->due_at?->isPast() && ! in_array($record->status, ['paid', 'cancelled']) ? 'danger' : null),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')->options([
                'draft' => 'Draft', 'sent' => 'Sent', 'paid' => 'Paid', 'overdue' => 'Overdue',
            ]),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make()->label('Update Payment'),
            Tables\Actions\Action::make('markPaid')
                ->label('Mark Paid')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn ($record) => in_array($record->status, ['sent', 'overdue']))
                ->action(function ($record) {
                    $record->update(['status' => 'paid', 'paid_at' => now()]);
                    Notification::make()->title('Invoice marked as paid.')->success()->send();
                }),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Invoice Details')->schema([
                Infolists\Components\TextEntry::make('invoice_number')->weight('bold'),
                Infolists\Components\TextEntry::make('client.company_name'),
                Infolists\Components\TextEntry::make('workOrder.reference_number')->placeholder('—'),
                Infolists\Components\TextEntry::make('status')->badge(),
                Infolists\Components\TextEntry::make('currency'),
            ])->columns(3),
            Infolists\Components\Section::make('Financial Summary')->schema([
                Infolists\Components\TextEntry::make('subtotal')->money('USD'),
                Infolists\Components\TextEntry::make('tax_rate')->suffix('%'),
                Infolists\Components\TextEntry::make('tax_amount')->money('USD'),
                Infolists\Components\TextEntry::make('total')->money('USD')->weight('bold')->size('lg'),
            ])->columns(4),
            Infolists\Components\Section::make('Payment')->schema([
                Infolists\Components\TextEntry::make('issued_at')->date(),
                Infolists\Components\TextEntry::make('due_at')->date(),
                Infolists\Components\TextEntry::make('paid_at')->date()->placeholder('Not yet paid'),
                Infolists\Components\TextEntry::make('payment_method')->placeholder('—'),
                Infolists\Components\TextEntry::make('payment_reference')->placeholder('—'),
            ])->columns(3),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Accountant\Resources\InvoiceResource\RelationManagers\InvoiceItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit'  => Pages\EditInvoice::route('/{record}/edit'),
            'view'  => Pages\ViewInvoice::route('/{record}'),
        ];
    }
}
