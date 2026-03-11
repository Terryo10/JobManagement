<?php
namespace App\Filament\Accountant\Widgets;

use App\Models\Expense;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PendingApprovalsWidget extends TableWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Pending Expense Approvals';

    public function table(Table $table): Table
    {
        return $table
            ->query(Expense::where('approval_status', 'pending')->latest())
            ->columns([
                Tables\Columns\TextColumn::make('workOrder.reference_number')->label('Job Card'),
                Tables\Columns\TextColumn::make('category')->badge(),
                Tables\Columns\TextColumn::make('amount')->money('USD'),
                Tables\Columns\TextColumn::make('submittedBy.name')->label('Submitted By'),
                Tables\Columns\TextColumn::make('expense_date')->date(),
            ])
            ->actions([
                Tables\Actions\Action::make('review')
                    ->url(fn (Expense $record): string => route('filament.accountant.resources.expenses.edit', ['record' => $record]))
                    ->icon('heroicon-m-pencil-square'),
            ])
            ->paginated([5]);
    }
}
