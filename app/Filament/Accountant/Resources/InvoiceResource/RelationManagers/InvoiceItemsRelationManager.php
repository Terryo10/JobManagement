<?php

namespace App\Filament\Accountant\Resources\InvoiceResource\RelationManagers;

use App\Models\RateCard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Line Items';

    public function form(Form $form): Form
    {
        return $form->schema([
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
                            $set('total', round(($get('quantity') ?: 1) * $rc->rate, 2));
                        }
                    }
                })
                ->columnSpanFull(),
            Forms\Components\TextInput::make('description')->required()->maxLength(255)->columnSpanFull(),
            Forms\Components\TextInput::make('quantity')->numeric()->required()->default(1)
                ->reactive()
                ->afterStateUpdated(fn (Set $set, Get $get) => $set('total', round(($get('quantity') ?: 0) * ($get('unit_price') ?: 0), 2))),
            Forms\Components\TextInput::make('unit')->maxLength(50),
            Forms\Components\TextInput::make('unit_price')->numeric()->required()->prefix('$')
                ->reactive()
                ->afterStateUpdated(fn (Set $set, Get $get) => $set('total', round(($get('quantity') ?: 0) * ($get('unit_price') ?: 0), 2))),
            Forms\Components\TextInput::make('total')->numeric()->prefix('$')->disabled()->dehydrated(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('description')->limit(50),
                Tables\Columns\TextColumn::make('rateCard.service_type')->label('Rate Card')->placeholder('Manual'),
                Tables\Columns\TextColumn::make('quantity')->numeric(2),
                Tables\Columns\TextColumn::make('unit'),
                Tables\Columns\TextColumn::make('unit_price')->money('usd'),
                Tables\Columns\TextColumn::make('total')->money('usd')->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(fn () => $this->recalculateInvoice()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(fn () => $this->recalculateInvoice()),
                \App\Filament\Shared\Actions\RequestDeletionTableAction::make()
                    ->after(fn () => $this->recalculateInvoice()),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()
                    ->after(fn () => $this->recalculateInvoice()),
            ])]);
    }

    private function recalculateInvoice(): void
    {
        $invoice = $this->getOwnerRecord();
        $subtotal = $invoice->items()->sum('total');
        $taxAmount = round($subtotal * ($invoice->tax_rate / 100), 2);
        $invoice->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $subtotal + $taxAmount,
        ]);
    }
}
