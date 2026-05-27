<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\StockLevelResource\Pages;
use App\Models\StockLevel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StockLevelResource extends Resource
{
    protected static ?string $model = StockLevel::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Warehouse';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('material_id')
                ->relationship('material', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->disabledOn('edit'),

            Forms\Components\TextInput::make('current_quantity')
                ->numeric()
                ->required()
                ->default(0)
                ->disabledOn('edit')
                ->helperText('Initial quantity for new stock records. To adjust existing stock, use the "Add Stock" or "Deduct Stock" actions from the list page.'),

            Forms\Components\DateTimePicker::make('last_updated')
                ->disabled(),

            Forms\Components\Select::make('last_updated_by')
                ->relationship('lastUpdatedBy', 'name')
                ->disabled(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('material.name')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('material.sku')
                ->label('SKU')
                ->searchable(),

            Tables\Columns\TextColumn::make('current_quantity')
                ->label('Current Quantity')
                ->sortable()
                ->weight('bold')
                ->suffix(fn ($record) => ' ' . ($record->material?->unit ?? '')),

            Tables\Columns\TextColumn::make('material.minimum_stock_level')
                ->label('Min Level'),

            Tables\Columns\TextColumn::make('lastUpdatedBy.name')
                ->label('Updated By')
                ->placeholder('—'),

            Tables\Columns\TextColumn::make('last_updated')
                ->label('Last Action')
                ->dateTime()
                ->sortable(),
        ])
        ->filters([
            Tables\Filters\Filter::make('low_stock')
                ->label('Low Stock Only')
                ->query(fn ($query) => $query->whereHas('material', function ($q) {
                    $q->whereColumn('stock_levels.current_quantity', '<=', 'materials.minimum_stock_level');
                })),
        ])
        ->actions([
            // ── Custom Add Stock Action ─────────────────────────────────────
            Tables\Actions\Action::make('add_stock')
                ->label('Add Stock')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->button()
                ->form([
                    Forms\Components\TextInput::make('quantity')
                        ->label('Quantity to Add')
                        ->numeric()
                        ->required()
                        ->minValue(0.01)
                        ->step(0.01),
                    Forms\Components\Textarea::make('notes')
                        ->label('Reason / Notes')
                        ->required()
                        ->rows(2)
                        ->placeholder('e.g. Received new shipment from supplier'),
                ])
                ->action(function (StockLevel $record, array $data) {
                    try {
                        \DB::transaction(function () use ($record, $data) {
                            $record->add(
                                qty: (float) $data['quantity'],
                                by: auth()->user(),
                                notes: $data['notes']
                            );
                        });
                        Notification::make()
                            ->title('Stock added successfully.')
                            ->body('The inventory ledger has been updated.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // ── Custom Deduct Stock Action ──────────────────────────────────
            Tables\Actions\Action::make('deduct_stock')
                ->label('Deduct Stock')
                ->icon('heroicon-o-minus-circle')
                ->color('danger')
                ->button()
                ->form([
                    Forms\Components\TextInput::make('quantity')
                        ->label('Quantity to Deduct')
                        ->numeric()
                        ->required()
                        ->minValue(0.01)
                        ->step(0.01),
                    Forms\Components\Textarea::make('notes')
                        ->label('Reason / Notes')
                        ->required()
                        ->rows(2)
                        ->placeholder('e.g. Stock adjustment / damaged items'),
                ])
                ->action(function (StockLevel $record, array $data) {
                    try {
                        \DB::transaction(function () use ($record, $data) {
                            $record->deduct(
                                qty: (float) $data['quantity'],
                                by: auth()->user(),
                                notes: $data['notes']
                            );
                        });
                        Notification::make()
                            ->title('Stock deducted successfully.')
                            ->body('The inventory ledger has been updated.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Tables\Actions\EditAction::make()->iconButton(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStockLevels::route('/'),
            'create' => Pages\CreateStockLevel::route('/create'),
            'edit'   => Pages\EditStockLevel::route('/{record}/edit'),
        ];
    }
}
