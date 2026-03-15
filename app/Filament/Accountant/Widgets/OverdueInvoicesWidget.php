<?php
namespace App\Filament\Accountant\Widgets;

use App\Models\Invoice;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class OverdueInvoicesWidget extends TableWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'half';
    protected static ?string $heading = 'Overdue Invoices';

    public function table(Table $table): Table
    {
        return $table
            ->query(Invoice::where('status', 'overdue')->orderBy('due_at', 'asc'))
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')->label('Invoice'),
                Tables\Columns\TextColumn::make('client.company_name')->label('Client')->limit(20),
                Tables\Columns\TextColumn::make('total')->money('USD'),
                Tables\Columns\TextColumn::make('due_at')->date()->color('danger'),
            ])
            ->recordUrl(fn (Invoice $record): string => route('filament.accountant.resources.invoices.view', ['record' => $record]))
            ->paginated([5]);
    }
}
