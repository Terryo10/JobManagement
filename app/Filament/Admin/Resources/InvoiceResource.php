<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InvoiceResource\Pages;
use App\Filament\Admin\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use App\Models\RateCard;
use App\Models\WorkOrder;
use App\Services\AiReportService;
use App\Services\InvoiceMailService;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
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
                        ->relationship('workOrder', 'reference_number')
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->reference_number} – {$record->title}")
                        ->searchable()->preload()
                        ->helperText('Link to a specific job card'),
                    Forms\Components\Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'pending_accountant' => 'Pending Accountant',
                            'pending_admin' => 'Pending Admin',
                            'approved' => 'Approved',
                            'sent' => 'Sent',
                            'paid' => 'Paid',
                            'signed' => 'Signed',
                            'overdue' => 'Overdue',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default('draft')->required(),
                    Forms\Components\TextInput::make('currency')->default('USD')->maxLength(10),
                    Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
                ])->columns(2),
                Forms\Components\Tabs\Tab::make('Line Items')->icon('heroicon-o-list-bullet')->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->schema([
                            Forms\Components\TextInput::make('description')->required()->maxLength(255),
                            Forms\Components\TextInput::make('quantity')
                                ->numeric()->default(1)->required()->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $qty = (float) ($get('quantity') ?? 0);
                                    $price = (float) ($get('unit_price') ?? 0);
                                    $set('total', round($qty * $price, 2));
                                    $items = $get('../../items') ?? [];
                                    $subtotal = collect($items)->reduce(fn ($carry, $item) => $carry + ((float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0)), 0);
                                    $set('../../subtotal', number_format($subtotal, 2, '.', ''));
                                    $taxRate = (float) ($get('../../tax_rate') ?? 0);
                                    $taxAmount = $subtotal * ($taxRate / 100);
                                    $set('../../tax_amount', number_format($taxAmount, 2, '.', ''));
                                    $set('../../total', number_format($subtotal + $taxAmount, 2, '.', ''));
                                }),
                            Forms\Components\TextInput::make('unit')->maxLength(255),
                            Forms\Components\TextInput::make('unit_price')
                                ->numeric()->required()->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $qty = (float) ($get('quantity') ?? 0);
                                    $price = (float) ($get('unit_price') ?? 0);
                                    $set('total', round($qty * $price, 2));
                                    $items = $get('../../items') ?? [];
                                    $subtotal = collect($items)->reduce(fn ($carry, $item) => $carry + ((float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0)), 0);
                                    $set('../../subtotal', number_format($subtotal, 2, '.', ''));
                                    $taxRate = (float) ($get('../../tax_rate') ?? 0);
                                    $taxAmount = $subtotal * ($taxRate / 100);
                                    $set('../../tax_amount', number_format($taxAmount, 2, '.', ''));
                                    $set('../../total', number_format($subtotal + $taxAmount, 2, '.', ''));
                                }),
                            Forms\Components\Hidden::make('total')->default(0)->dehydrated(),
                        ])
                        ->columns(4)->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            $items = $get('items') ?? [];
                            $subtotal = collect($items)->reduce(fn ($carry, $item) => $carry + ((float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0)), 0);
                            $set('subtotal', number_format($subtotal, 2, '.', ''));
                            $taxRate = (float) ($get('tax_rate') ?? 0);
                            $taxAmount = $subtotal * ($taxRate / 100);
                            $set('tax_amount', number_format($taxAmount, 2, '.', ''));
                            $set('total', number_format($subtotal + $taxAmount, 2, '.', ''));
                        })->addActionLabel('Add Line Item'),
                ]),
                Forms\Components\Tabs\Tab::make('Financials')->icon('heroicon-o-calculator')->schema([
                    Forms\Components\TextInput::make('subtotal')->numeric()->prefix('$')->default(0)->readOnly(),
                    Forms\Components\TextInput::make('tax_rate')->numeric()->suffix('%')->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            $items = $get('items') ?? [];
                            $subtotal = collect($items)->reduce(fn ($carry, $item) => $carry + ((float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0)), 0);
                            $set('subtotal', number_format($subtotal, 2, '.', ''));
                            $taxRate = (float) ($get('tax_rate') ?: 0);
                            $taxAmount = $subtotal * ($taxRate / 100);
                            $set('tax_amount', number_format($taxAmount, 2, '.', ''));
                            $set('total', number_format($subtotal + $taxAmount, 2, '.', ''));
                        }),
                    Forms\Components\TextInput::make('tax_amount')->numeric()->prefix('$')->default(0)->readOnly(),
                    Forms\Components\TextInput::make('total')->numeric()->prefix('$')->default(0)->readOnly(),
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
                'draft' => 'gray', 'pending_accountant' => 'warning', 'pending_admin' => 'warning', 'approved' => 'success',
                'sent' => 'info', 'signed' => 'success', 'paid' => 'success',
                'overdue' => 'danger', 'cancelled' => 'gray', default => 'gray',
            }),
            Tables\Columns\TextColumn::make('total')->money('USD')->sortable(),
            Tables\Columns\TextColumn::make('issued_at')->date()->sortable(),
            Tables\Columns\TextColumn::make('due_at')->date()->sortable()
                ->color(fn ($record) => $record->due_at && $record->due_at->isPast() && $record->status !== 'paid' ? 'danger' : null),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')->options([
                'draft' => 'Draft', 'pending_accountant' => 'Pending Accountant', 'pending_admin' => 'Pending Admin', 'approved' => 'Approved', 'sent' => 'Sent', 'signed' => 'Signed', 'paid' => 'Paid', 'overdue' => 'Overdue', 'cancelled' => 'Cancelled',
            ]),
            Tables\Filters\TrashedFilter::make(),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('approveAdmin')
                ->label('Approve Invoice')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn ($record) => $record->status === 'pending_admin')
                ->action(function ($record) {
                    $record->update(['status' => 'approved']);
                    Notification::make()->title('Invoice fully approved.')->success()->send();
                }),
            Tables\Actions\Action::make('send')
                ->label('Email Invoice')
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->visible(fn ($record) => in_array($record->status, ['approved', 'sent', 'signed', 'overdue']))
                ->form([
                    Forms\Components\TextInput::make('email')
                        ->label('Recipient Email')
                        ->email()
                        ->required()
                        ->default(fn ($record) => $record->client?->email),
                ])
                ->action(function ($record, array $data) {
                    $email = $data['email'];

                    $record->update([
                        'status' => 'sent',
                        'issued_at' => $record->issued_at ?? now(),
                    ]);
                    
                    // Dispatch the email with the signed URL
                    app(InvoiceMailService::class)->sendInvoiceToClient($record, $email);

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
                    
                    \Filament\Notifications\Notification::make()->title('Invoice emailed successfully.')->success()->send();
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
            Tables\Actions\Action::make('aiNotes')
                ->label('AI Notes')
                ->icon('heroicon-o-sparkles')
                ->color('info')
                ->modalHeading('AI-Generated Invoice Notes')
                ->modalDescription('Review the AI-generated notes and click "Use These Notes" to apply them.')
                ->form([
                    Forms\Components\Textarea::make('generated_notes')
                        ->label('Generated Notes')
                        ->rows(5)
                        ->default(function ($record) {
                            $record->load('items', 'client', 'workOrder');
                            return (new AiReportService())->generateInvoiceNotes($record);
                        })
                        ->required(),
                ])
                ->action(function ($record, array $data) {
                    $record->update(['notes' => $data['generated_notes']]);
                    Notification::make()->title('Notes updated.')->success()->send();
                })
                ->modalSubmitActionLabel('Use These Notes'),
            Tables\Actions\Action::make('aiLineItems')
                ->label('AI Line Items')
                ->icon('heroicon-o-list-bullet')
                ->color('warning')
                ->visible(fn ($record) => $record->work_order_id !== null)
                ->modalHeading('AI-Suggested Line Items')
                ->modalDescription('The AI will suggest line items based on the linked work order\'s tasks and expenses. Confirming will add them to this invoice.')
                ->requiresConfirmation()
                ->modalSubmitActionLabel('Add Line Items')
                ->action(function ($record) {
                    $record->load('workOrder.tasks', 'workOrder.expenses');
                    $items = (new AiReportService())->suggestInvoiceItems($record);

                    if (empty($items)) {
                        Notification::make()->title('No items could be generated. Make sure the work order has tasks or expenses.')->warning()->send();
                        return;
                    }

                    $subtotal = 0;
                    foreach ($items as $item) {
                        $total = round((float) ($item['quantity'] ?? 1) * (float) ($item['unit_price'] ?? 0), 2);
                        $record->items()->create([
                            'description' => $item['description'] ?? 'Service',
                            'quantity'    => $item['quantity'] ?? 1,
                            'unit'        => $item['unit'] ?? 'each',
                            'unit_price'  => $item['unit_price'] ?? 0,
                            'total'       => $total,
                        ]);
                        $subtotal += $total;
                    }

                    $taxAmount = round($subtotal * (($record->tax_rate ?? 0) / 100), 2);
                    $record->update([
                        'subtotal'   => $subtotal,
                        'tax_amount' => $taxAmount,
                        'total'      => $subtotal + $taxAmount,
                    ]);

                    Notification::make()->title(count($items) . ' line items added.')->success()->send();
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
            \App\Filament\Admin\Resources\InvoiceResource\RelationManagers\DocumentsRelationManager::class,
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
