<?php

namespace App\Filament\Accountant\Resources;

use App\Filament\Accountant\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;
    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationLabel = 'Expenses';
    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Approval')->schema([
                Forms\Components\Select::make('approval_status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])
                    ->required(),
                Forms\Components\Hidden::make('approved_by')->default(fn () => auth()->id()),
                Forms\Components\Textarea::make('rejection_reason')->rows(3)
                    ->visible(fn (Forms\Get $get) => $get('approval_status') === 'rejected')
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('workOrder.reference_number')->label('Job Card'),
            Tables\Columns\TextColumn::make('category')->badge(),
            Tables\Columns\TextColumn::make('amount')->money('USD')->sortable(),
            Tables\Columns\TextColumn::make('expense_date')->date()->sortable(),
            Tables\Columns\TextColumn::make('submittedBy.name')->label('Submitted By'),
            Tables\Columns\TextColumn::make('approval_status')->badge()->color(fn ($state) => match ($state) {
                'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', default => 'gray',
            }),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('approval_status')->options([
                'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected',
            ]),
        ])
        ->actions([
            Tables\Actions\EditAction::make()->label('Review'),
            Tables\Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn ($record) => $record->approval_status === 'pending')
                ->action(function ($record) {
                    $record->update(['approval_status' => 'approved', 'approved_by' => auth()->id()]);
                    Notification::make()->title('Expense approved.')->success()->send();
                }),
            Tables\Actions\Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    Forms\Components\Textarea::make('rejection_reason')->required()->rows(3),
                ])
                ->visible(fn ($record) => $record->approval_status === 'pending')
                ->action(function ($record, array $data) {
                    $record->update([
                        'approval_status' => 'rejected',
                        'approved_by' => auth()->id(),
                        'rejection_reason' => $data['rejection_reason'],
                    ]);
                    Notification::make()->title('Expense rejected.')->warning()->send();
                }),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'edit'  => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
