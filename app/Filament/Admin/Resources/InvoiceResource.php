<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InvoiceResource\Pages;
use App\Filament\Admin\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use App\Models\WorkOrder;
use App\Models\RateCard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceSentToClient;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 2;

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
                        ->options(['draft' => 'Draft', 'sent' => 'Sent', 'paid' => 'Paid', 'signed' => 'Signed', 'overdue' => 'Overdue', 'cancelled' => 'Cancelled'])
                        ->default('draft')->required(),
                    Forms\Components\TextInput::make('currency')->default('USD')->maxLength(10),
                    Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
                ])->columns(2),
                Forms\Components\Tabs\Tab::make('Financials')->icon('heroicon-o-calculator')->schema([
                    Forms\Components\TextInput::make('subtotal')->numeric()->prefix('$')->default(0)->disabled()->dehydrated(),
                    Forms\Components\TextInput::make('tax_rate')->numeric()->suffix('%')->default(0)
                        ->reactive()
                        ->afterStateUpdated(function (Set $set, Get $get) {
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
            Tables\Columns\TextColumn::make('client.company_name')->sortable()->limit(25),
            Tables\Columns\TextColumn::make('workOrder.reference_number')->label('Job Card')->placeholder('—'),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                'draft' => 'gray', 'sent' => 'info', 'signed' => 'success', 'paid' => 'success',
                'overdue' => 'danger', 'cancelled' => 'gray', default => 'gray',
            }),
            Tables\Columns\TextColumn::make('total')->money('USD')->sortable(),
            Tables\Columns\TextColumn::make('issued_at')->date()->sortable(),
            Tables\Columns\TextColumn::make('due_at')->date()->sortable()
                ->color(fn ($record) => $record->due_at && $record->due_at->isPast() && $record->status !== 'paid' ? 'danger' : null),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')->options([
                'draft' => 'Draft', 'sent' => 'Sent', 'signed' => 'Signed', 'paid' => 'Paid', 'overdue' => 'Overdue', 'cancelled' => 'Cancelled',
            ]),
            Tables\Filters\TrashedFilter::make(),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('send')
                ->label('Send to Client')
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->requiresConfirmation()
                ->action(function ($record) {
                    if (!$record->client || !$record->client->email) {
                        \Filament\Notifications\Notification::make()->title('Client has no email attached.')->danger()->send();
                        return;
                    }

                    $record->update([
                        'status' => 'sent',
                        'issued_at' => $record->issued_at ?? now(),
                    ]);
                    
                    // Dispatch the email with the signed URL
                    Mail::to($record->client->email)->send(new InvoiceSentToClient($record));

                    // Notify the client user in the dashboard if they exist
                    $clientUser = \App\Models\User::where('email', $record->client?->email)->first();
                    if ($clientUser) {
                        $signedUrl = route('invoices.sign.show', ['invoice' => $record->id]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('New Invoice Received')
                            ->body("Invoice {$record->invoice_number} for \${$record->total} has been sent to you. Click below to review and sign.")
                            ->icon('heroicon-o-document-currency-dollar')
                            ->info()
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('sign')
                                    ->label('Review & Sign')
                                    ->url($signedUrl)
                                    ->openUrlInNewTab()
                                    ->button()
                            ])
                            ->sendToDatabase($clientUser);
                    }
                    
                    \Filament\Notifications\Notification::make()->title('Invoice sent to client.')->success()->send();
                }),
            Tables\Actions\Action::make('markPaid')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn ($record) => in_array($record->status, ['sent', 'signed', 'overdue']))
                ->action(function ($record) {
                    $record->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);
                    // Notify the client user
                    $clientUser = \App\Models\User::where('email', $record->client?->email)->first();
                    if ($clientUser) {
                        \Filament\Notifications\Notification::make()
                            ->title('Payment Confirmed')
                            ->body("Invoice {$record->invoice_number} has been marked as paid. Thank you!")
                            ->icon('heroicon-o-check-badge')
                            ->success()
                            ->sendToDatabase($clientUser);
                    }
                }),
            Tables\Actions\Action::make('downloadPdf')
                ->label('Download PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(function ($record) {
                    $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $record]);
                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        "invoice-{$record->invoice_number}.pdf"
                    );
                }),
        ])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Invoice Details')->schema([
                Infolists\Components\TextEntry::make('invoice_number'),
                Infolists\Components\TextEntry::make('client.company_name'),
                Infolists\Components\TextEntry::make('workOrder.reference_number')->label('Job Card'),
                Infolists\Components\TextEntry::make('status')->badge(),
                Infolists\Components\TextEntry::make('subtotal')->money('usd'),
                Infolists\Components\TextEntry::make('tax_rate')->suffix('%'),
                Infolists\Components\TextEntry::make('tax_amount')->money('usd'),
                Infolists\Components\TextEntry::make('total')->money('usd')->weight('bold'),
                Infolists\Components\TextEntry::make('issued_at')->date(),
                Infolists\Components\TextEntry::make('due_at')->date(),
                Infolists\Components\TextEntry::make('paid_at')->dateTime(),
                Infolists\Components\TextEntry::make('notes')->columnSpanFull(),
            ])->columns(4),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\InvoiceItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view'   => Pages\ViewInvoice::route('/{record}'),
            'edit'   => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
