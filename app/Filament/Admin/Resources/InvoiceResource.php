<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
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
            Forms\Components\TextInput::make('invoice_number')->required()->maxLength(50)->unique(ignoreRecord: true),
            Forms\Components\Select::make('client_id')->relationship('client', 'company_name')->searchable()->preload()->required(),
            Forms\Components\Select::make('work_order_id')->relationship('workOrder', 'reference_number')->searchable()->preload(),
            Forms\Components\Select::make('status')->options(['draft' => 'Draft', 'sent' => 'Sent', 'paid' => 'Paid', 'overdue' => 'Overdue', 'cancelled' => 'Cancelled'])->default('draft')->required(),
            Forms\Components\TextInput::make('subtotal')->numeric()->prefix('USD')->default(0),
            Forms\Components\TextInput::make('tax_rate')->numeric()->suffix('%')->default(0),
            Forms\Components\TextInput::make('tax_amount')->numeric()->prefix('USD')->default(0),
            Forms\Components\TextInput::make('total')->numeric()->prefix('USD')->default(0),
            Forms\Components\TextInput::make('currency')->default('USD')->maxLength(10),
            Forms\Components\DatePicker::make('issued_at'),
            Forms\Components\DatePicker::make('due_at'),
            Forms\Components\TextInput::make('payment_method')->maxLength(100),
            Forms\Components\TextInput::make('payment_reference'),
            Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('invoice_number')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('client.company_name')->sortable(),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) { 'draft' => 'gray', 'sent' => 'info', 'paid' => 'success', 'overdue' => 'danger', 'cancelled' => 'gray', default => 'gray' }),
            Tables\Columns\TextColumn::make('total')->money('USD')->sortable(),
            Tables\Columns\TextColumn::make('issued_at')->date()->sortable(),
            Tables\Columns\TextColumn::make('due_at')->date()->sortable(),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')->options(['draft' => 'Draft', 'sent' => 'Sent', 'paid' => 'Paid', 'overdue' => 'Overdue', 'cancelled' => 'Cancelled']),
            Tables\Filters\TrashedFilter::make(),
        ])
        ->actions([Tables\Actions\EditAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit'   => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
