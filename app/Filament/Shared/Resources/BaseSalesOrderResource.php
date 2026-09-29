<?php

namespace App\Filament\Shared\Resources;

use App\Filament\Shared\Concerns\EnforcesAdminDelete;
use App\Models\SalesOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

abstract class BaseSalesOrderResource extends Resource
{
    use EnforcesAdminDelete;

    protected static ?string $model = SalesOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Sales Orders';

    protected static ?string $modelLabel = 'Sales Order';

    protected static ?string $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 4;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Order Details')->schema([
                Forms\Components\TextInput::make('order_number')->disabled()->dehydrated(false),
                Forms\Components\Select::make('status')->options([
                    'draft' => 'Draft',
                    'confirmed' => 'Confirmed',
                    'in_progress' => 'In Progress',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                ])->required(),
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'company_name')->searchable()->preload()->required()
                    ->disabled()->dehydrated(false),
                Forms\Components\Select::make('quotation_id')
                    ->relationship('quotation', 'quotation_number')->disabled()->dehydrated(false),
                Forms\Components\Select::make('work_order_id')
                    ->relationship('workOrder', 'reference_number')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->reference_number} — {$record->title}")
                    ->searchable()->preload()->label('Job Card'),
                Forms\Components\Select::make('currency')
                    ->options(['USD' => 'USD', 'ZWG' => 'ZWG'])->required()
                    ->disabled()->dehydrated(false),
                Forms\Components\DatePicker::make('order_date')->required(),
                Forms\Components\DatePicker::make('required_date'),
                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ])->columns(2),
            Forms\Components\Section::make('Line Items')->schema([
                Forms\Components\Repeater::make('items')->relationship()->schema([
                    Forms\Components\TextInput::make('description')->required()->columnSpan(2),
                    Forms\Components\TextInput::make('quantity')->numeric()->required(),
                    Forms\Components\TextInput::make('unit'),
                    Forms\Components\TextInput::make('unit_price')->numeric()->required(),
                    Forms\Components\TextInput::make('total')->numeric()->required(),
                ])->columns(4)->disabled()->dehydrated(false),
            ]),
            Forms\Components\Section::make('Totals')->schema([
                Forms\Components\TextInput::make('subtotal')->numeric()->disabled()->dehydrated(false),
                Forms\Components\TextInput::make('tax_rate')->numeric()->suffix('%')->disabled()->dehydrated(false),
                Forms\Components\TextInput::make('tax_amount')->numeric()->disabled()->dehydrated(false),
                Forms\Components\TextInput::make('total')->numeric()->disabled()->dehydrated(false),
            ])->columns(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')->label('Order #')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('quotation.quotation_number')->label('Quotation')->searchable(),
                Tables\Columns\TextColumn::make('client.company_name')->label('Client')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('workOrder.reference_number')->label('Job Card')->placeholder('—'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'confirmed' => 'info', 'in_progress' => 'warning', 'completed' => 'success',
                    'cancelled' => 'danger', default => 'gray',
                }),
                Tables\Columns\TextColumn::make('total')
                    ->formatStateUsing(fn ($state, $record) => "{$record->currency} ".number_format((float) $state, 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('required_date')->date()->placeholder('—')->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'draft' => 'Draft', 'confirmed' => 'Confirmed', 'in_progress' => 'In Progress',
                    'completed' => 'Completed', 'cancelled' => 'Cancelled',
                ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Sales Order')->schema([
                Infolists\Components\TextEntry::make('order_number')->label('Order #')->weight('bold'),
                Infolists\Components\TextEntry::make('status')->badge(),
                Infolists\Components\TextEntry::make('client.company_name')->label('Client'),
                Infolists\Components\TextEntry::make('quotation.quotation_number')->label('Source Quotation'),
                Infolists\Components\TextEntry::make('workOrder.reference_number')->label('Job Card')->placeholder('—'),
                Infolists\Components\TextEntry::make('order_date')->date(),
                Infolists\Components\TextEntry::make('required_date')->date()->placeholder('—'),
                Infolists\Components\TextEntry::make('total')
                    ->formatStateUsing(fn ($state, $record) => "{$record->currency} ".number_format((float) $state, 2))
                    ->weight('bold'),
                Infolists\Components\TextEntry::make('notes')->columnSpanFull()->placeholder('—'),
            ])->columns(4),
            Infolists\Components\Section::make('Line Items')->schema([
                Infolists\Components\RepeatableEntry::make('items')->schema([
                    Infolists\Components\TextEntry::make('description')->columnSpan(2),
                    Infolists\Components\TextEntry::make('quantity'),
                    Infolists\Components\TextEntry::make('unit')->placeholder('—'),
                    Infolists\Components\TextEntry::make('unit_price')->numeric(decimalPlaces: 2),
                    Infolists\Components\TextEntry::make('total')->numeric(decimalPlaces: 2),
                ])->columns(6),
            ]),
        ]);
    }
}
