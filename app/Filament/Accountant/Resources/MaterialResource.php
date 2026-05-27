<?php

namespace App\Filament\Accountant\Resources;

use App\Filament\Accountant\Resources\MaterialResource\Pages;
use App\Models\Material;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MaterialResource extends Resource
{
    protected static ?string $model = Material::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Inventory';
    protected static ?string $modelLabel = 'Inventory Item';
    protected static ?string $pluralModelLabel = 'Inventory Items';
    protected static ?string $navigationGroup = 'Warehouse';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('sku')
                ->label('SKU')
                ->required()
                ->maxLength(100)
                ->unique(ignoreRecord: true),

            Forms\Components\TextInput::make('category')
                ->maxLength(100)
                ->placeholder('e.g. Tools, Materials, Safety'),

            Forms\Components\TextInput::make('unit')
                ->label('Unit of Measure')
                ->required()
                ->maxLength(50)
                ->placeholder('e.g. units, sqm, metres, kg'),

            Forms\Components\TextInput::make('minimum_stock_level')
                ->numeric()
                ->default(0),

            Forms\Components\TextInput::make('reorder_quantity')
                ->numeric(),

            Forms\Components\TextInput::make('unit_cost')
                ->numeric()
                ->prefix('USD'),

            Forms\Components\Select::make('preferred_supplier_id')
                ->relationship('preferredSupplier', 'name')
                ->searchable()
                ->preload()
                ->label('Preferred Supplier'),

            Forms\Components\Toggle::make('is_active')
                ->default(true),

            Forms\Components\Textarea::make('description')
                ->rows(3)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('sku')
                ->label('SKU')
                ->searchable(),

            Tables\Columns\TextColumn::make('category')
                ->badge(),

            Tables\Columns\TextColumn::make('unit')
                ->label('Unit'),

            Tables\Columns\TextColumn::make('stockLevel.current_quantity')
                ->label('In Stock')
                ->weight('bold')
                ->placeholder('0.00'),

            Tables\Columns\TextColumn::make('minimum_stock_level')
                ->label('Min Stock'),

            Tables\Columns\TextColumn::make('unit_cost')
                ->money('USD'),

            Tables\Columns\IconColumn::make('is_active')
                ->boolean()
                ->label('Active'),
        ])
        ->filters([
            Tables\Filters\TernaryFilter::make('is_active')
                ->label('Active Items Only'),
            Tables\Filters\SelectFilter::make('category')
                ->options(fn () => Material::pluck('category', 'category')->filter()->toArray()),
        ])
        ->headerActions([
            // ── Export All Header Action ─────────────────────────────────────
            Tables\Actions\Action::make('export_all')
                ->label('Export All to CSV')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(fn () => self::downloadCsv(Material::with('stockLevel')->get())),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                // ── Export Selected Bulk Action ─────────────────────────────
                Tables\Actions\BulkAction::make('export_selected')
                    ->label('Export Selected to CSV')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(fn ($records) => self::downloadCsv($records)),

                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
    }

    /**
     * CSV Download Logic helper.
     */
    public static function downloadCsv($records)
    {
        $csv = [];
        $csv[] = ['Name', 'SKU', 'Category', 'Unit', 'In Stock', 'Min Stock Level', 'Unit Cost (USD)', 'Status'];

        foreach ($records as $record) {
            $csv[] = [
                $record->name,
                $record->sku,
                $record->category ?? '—',
                $record->unit,
                $record->stockLevel?->current_quantity ?? 0,
                $record->minimum_stock_level ?? 0,
                $record->unit_cost ?? 0,
                $record->is_active ? 'Active' : 'Inactive',
            ];
        }

        $output = '';
        foreach ($csv as $row) {
            $fh = fopen('php://temp', 'r+');
            fputcsv($fh, $row);
            rewind($fh);
            $output .= stream_get_contents($fh);
            fclose($fh);
        }

        return response()->streamDownload(function () use ($output) {
            echo $output;
        }, 'inventory-export-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMaterials::route('/'),
            'create' => Pages\CreateMaterial::route('/create'),
            'edit'   => Pages\EditMaterial::route('/{record}/edit'),
        ];
    }
}
