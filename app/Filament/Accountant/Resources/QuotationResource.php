<?php

namespace App\Filament\Accountant\Resources;

use App\Filament\Accountant\Resources\QuotationResource\Pages;
use App\Filament\Accountant\Resources\QuotationResource\RelationManagers\DocumentsRelationManager;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\RateCard;
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
use App\Filament\Shared\Concerns\EnforcesAdminDelete;

class QuotationResource extends Resource
{
    use EnforcesAdminDelete;
    protected static ?string $model = Quotation::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $navigationLabel = 'Quotations';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Quotation')->tabs([
                Forms\Components\Tabs\Tab::make('Details')->icon('heroicon-o-information-circle')->schema([
                    Forms\Components\TextInput::make('quotation_number')->required()->maxLength(50)
                        ->unique(ignoreRecord: true)
                        ->default(fn () => 'QUO-' . now()->format('Y') . '-' . str_pad(Quotation::count() + 1, 4, '0', STR_PAD_LEFT)),
                    Forms\Components\Select::make('client_id')
                        ->relationship('client', 'company_name')->searchable()->preload()->required(),
                    Forms\Components\Select::make('work_order_id')
                        ->relationship('workOrder', 'reference_number')
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->reference_number} – {$record->title}")
                        ->searchable()->preload()
                        ->helperText('Link to a specific job card'),
                    Forms\Components\Select::make('status')
                        ->options([
                            'draft'     => 'Draft',
                            'sent'      => 'Sent',
                            'accepted'  => 'Accepted',
                            'rejected'  => 'Rejected',
                            'expired'   => 'Expired',
                            'converted' => 'Converted to Invoice',
                        ])
                        ->default('draft')->required(),
                    Forms\Components\TextInput::make('currency')->default('USD')->maxLength(10),
                    Forms\Components\DatePicker::make('valid_until')->label('Valid Until'),
                    Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make('Line Items')->icon('heroicon-o-list-bullet')->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->schema([
                            Forms\Components\Select::make('rate_card_id')
                                ->label('Rate Card')
                                ->options(RateCard::where('is_active', true)->get()->mapWithKeys(fn ($rc) => [
                                    $rc->id => "{$rc->service_type} — {$rc->category} (\${$rc->rate}/{$rc->unit})",
                                ]))
                                ->searchable()
                                ->reactive()
                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                    if ($state) {
                                        $rc = RateCard::find($state);
                                        if ($rc) {
                                            $set('description', "{$rc->service_type} — {$rc->category}");
                                            $set('unit', $rc->unit);
                                            $set('unit_price', $rc->rate);
                                            $qty = (float) ($get('quantity') ?: 1);
                                            $set('total', round($qty * $rc->rate, 2));
                                        }
                                    }
                                })
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('description')->required()->maxLength(255),
                            Forms\Components\TextInput::make('quantity')
                                ->numeric()->default(1)->required()->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $qty = (float) ($get('quantity') ?? 0);
                                    $price = (float) ($get('unit_price') ?? 0);
                                    $set('total', round($qty * $price, 2));
                                    self::recalcTotals($get, $set);
                                }),
                            Forms\Components\TextInput::make('unit')->maxLength(50),
                            Forms\Components\TextInput::make('unit_price')
                                ->numeric()->required()->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $qty = (float) ($get('quantity') ?? 0);
                                    $price = (float) ($get('unit_price') ?? 0);
                                    $set('total', round($qty * $price, 2));
                                    self::recalcTotals($get, $set);
                                }),
                            Forms\Components\Hidden::make('total')->default(0)->dehydrated(),
                        ])
                        ->columns(4)->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            $items = $get('items') ?? [];
                            $subtotal = collect($items)->reduce(fn ($c, $i) => $c + ((float) ($i['quantity'] ?? 0) * (float) ($i['unit_price'] ?? 0)), 0);
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
                            $subtotal = collect($items)->reduce(fn ($c, $i) => $c + ((float) ($i['quantity'] ?? 0) * (float) ($i['unit_price'] ?? 0)), 0);
                            $set('subtotal', number_format($subtotal, 2, '.', ''));
                            $taxRate = (float) ($get('tax_rate') ?: 0);
                            $taxAmount = $subtotal * ($taxRate / 100);
                            $set('tax_amount', number_format($taxAmount, 2, '.', ''));
                            $set('total', number_format($subtotal + $taxAmount, 2, '.', ''));
                        }),
                    Forms\Components\TextInput::make('tax_amount')->numeric()->prefix('$')->default(0)->readOnly(),
                    Forms\Components\TextInput::make('total')->numeric()->prefix('$')->default(0)->readOnly(),
                ])->columns(2),
            ])->columnSpanFull(),
        ]);
    }

    private static function recalcTotals(Get $get, Set $set): void
    {
        $items = $get('../../items') ?? [];
        $subtotal = collect($items)->reduce(fn ($c, $i) => $c + ((float) ($i['quantity'] ?? 0) * (float) ($i['unit_price'] ?? 0)), 0);
        $set('../../subtotal', number_format($subtotal, 2, '.', ''));
        $taxRate = (float) ($get('../../tax_rate') ?? 0);
        $taxAmount = $subtotal * ($taxRate / 100);
        $set('../../tax_amount', number_format($taxAmount, 2, '.', ''));
        $set('../../total', number_format($subtotal + $taxAmount, 2, '.', ''));
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('quotation_number')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('client.company_name')->label('Client')->sortable()->limit(25),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                'draft'     => 'gray',
                'sent'      => 'info',
                'accepted'  => 'success',
                'rejected'  => 'danger',
                'expired'   => 'warning',
                'converted' => 'purple',
                default     => 'gray',
            }),
            Tables\Columns\TextColumn::make('total')->money('USD')->sortable(),
            Tables\Columns\TextColumn::make('valid_until')->date()->sortable()
                ->color(fn ($record) => $record->valid_until?->isPast() && ! in_array($record->status, ['accepted', 'converted']) ? 'danger' : null),
            Tables\Columns\TextColumn::make('created_at')->date()->sortable()->toggleable(),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')->options([
                'draft' => 'Draft', 'sent' => 'Sent', 'accepted' => 'Accepted',
                'rejected' => 'Rejected', 'expired' => 'Expired', 'converted' => 'Converted',
            ]),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('downloadPdf')
                ->label('Download PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(function ($record) {
                    $record->load('items', 'client', 'workOrder', 'createdBy');
                    $pdf = Pdf::loadView('pdf.quotation', ['quotation' => $record]);
                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        "quotation-{$record->quotation_number}.pdf"
                    );
                }),
            Tables\Actions\Action::make('convertToInvoice')
                ->label('Convert to Invoice')
                ->icon('heroicon-o-arrow-right-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Convert Quotation to Invoice')
                ->modalDescription('This will create a new Invoice from this quotation and mark the quotation as converted.')
                ->visible(fn ($record) => in_array($record->status, ['sent', 'accepted']))
                ->action(function ($record) {
                    $record->load('items');
                    $invoice = Invoice::create([
                        'invoice_number' => 'INV-' . now()->format('Y') . '-' . str_pad(Invoice::count() + 1, 4, '0', STR_PAD_LEFT),
                        'client_id'      => $record->client_id,
                        'work_order_id'  => $record->work_order_id,
                        'status'         => 'draft',
                        'currency'       => $record->currency,
                        'subtotal'       => $record->subtotal,
                        'tax_rate'       => $record->tax_rate,
                        'tax_amount'     => $record->tax_amount,
                        'total'          => $record->total,
                        'notes'          => $record->notes,
                        'created_by'     => auth()->id(),
                    ]);
                    foreach ($record->items as $item) {
                        $invoice->items()->create([
                            'description'  => $item->description,
                            'quantity'     => $item->quantity,
                            'unit'         => $item->unit,
                            'unit_price'   => $item->unit_price,
                            'total'        => $item->total,
                            'rate_card_id' => $item->rate_card_id,
                        ]);
                    }
                    $record->update(['status' => 'converted']);
                    Notification::make()->title("Invoice {$invoice->invoice_number} created successfully.")->success()->send();
                }),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Quotation Details')->schema([
                Infolists\Components\TextEntry::make('quotation_number')->weight('bold'),
                Infolists\Components\TextEntry::make('client.company_name'),
                Infolists\Components\TextEntry::make('workOrder.reference_number')->placeholder('—'),
                Infolists\Components\TextEntry::make('status')->badge(),
                Infolists\Components\TextEntry::make('currency'),
                Infolists\Components\TextEntry::make('valid_until')->date()->placeholder('—'),
            ])->columns(3),
            Infolists\Components\Section::make('Financial Summary')->schema([
                Infolists\Components\TextEntry::make('subtotal')->money('USD'),
                Infolists\Components\TextEntry::make('tax_rate')->suffix('%'),
                Infolists\Components\TextEntry::make('tax_amount')->money('USD'),
                Infolists\Components\TextEntry::make('total')->money('USD')->weight('bold')->size('lg'),
            ])->columns(4),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListQuotations::route('/'),
            'create' => Pages\CreateQuotation::route('/create'),
            'edit'   => Pages\EditQuotation::route('/{record}/edit'),
            'view'   => Pages\ViewQuotation::route('/{record}'),
        ];
    }
}
