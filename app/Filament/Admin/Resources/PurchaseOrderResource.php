<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PurchaseOrderResource\Pages;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('reference_number')->required()->maxLength(50)->unique(ignoreRecord: true),
            Forms\Components\Select::make('supplier_id')->relationship('supplier', 'name')->searchable()->preload()->required(),
            Forms\Components\Select::make('status')->options(['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'ordered' => 'Ordered', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'])->default('draft')->required(),
            Forms\Components\Select::make('ordered_by')->relationship('orderedBy', 'name')->searchable()->preload()->required(),
            Forms\Components\Select::make('approved_by')->relationship('approvedBy', 'name')->searchable()->preload(),
            Forms\Components\TextInput::make('total_amount')->numeric()->prefix('USD')->default(0),
            Forms\Components\DatePicker::make('expected_delivery'),
            Forms\Components\DateTimePicker::make('delivered_at'),
            Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('reference_number')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('supplier.name')->sortable(),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) { 'draft' => 'gray', 'submitted' => 'info', 'approved' => 'warning', 'ordered' => 'warning', 'delivered' => 'success', 'cancelled' => 'danger', default => 'gray' }),
            Tables\Columns\TextColumn::make('total_amount')->money('USD')->sortable(),
            Tables\Columns\TextColumn::make('expected_delivery')->date()->sortable(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([Tables\Filters\SelectFilter::make('status')->options(['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'ordered' => 'Ordered', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'])])
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
            'index'  => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'edit'   => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
