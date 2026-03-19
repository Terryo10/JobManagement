<?php

namespace App\Filament\Accountant\Widgets;

use App\Models\Invoice;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentActivityWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Recent Invoice Activity';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Invoice::query()
                    ->with(['client'])
                    ->orderBy('updated_at', 'desc')
                    ->limit(8)
            )
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice')
                    ->searchable()
                    ->url(fn ($record) => '/accountant/invoices/' . $record->id),

                Tables\Columns\TextColumn::make('client.company_name')
                    ->label('Client')
                    ->limit(25),

                Tables\Columns\TextColumn::make('total')
                    ->label('Amount')
                    ->money('USD'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'draft'              => 'gray',
                        'pending_accountant' => 'warning',
                        'pending_admin'      => 'warning',
                        'approved'           => 'success',
                        'sent'               => 'info',
                        'signed'             => 'success',
                        'paid'               => 'success',
                        'overdue'            => 'danger',
                        'cancelled'          => 'gray',
                        default              => 'gray',
                    }),

                Tables\Columns\TextColumn::make('due_at')
                    ->label('Due')
                    ->date()
                    ->color(fn ($record) => $record->due_at?->isPast() && !in_array($record->status, ['paid', 'cancelled']) ? 'danger' : null),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->since()
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
