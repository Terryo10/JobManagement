<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Shared\Concerns\EnforcesAdminDelete;

class ExpenseResource extends Resource
{
    use EnforcesAdminDelete;
    protected static ?string $model = Expense::class;
    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationLabel = 'My Expenses';
    protected static ?string $modelLabel = 'Expense';
    protected static ?string $pluralModelLabel = 'Expenses';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('submitted_by', auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\Select::make('work_order_id')
                    ->relationship('workOrder', 'reference_number')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->reference_number} – {$record->title}")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Work Order')
                    ->columnSpanFull(),
                Forms\Components\Select::make('category')
                    ->options([
                        'Labour'    => 'Labour',
                        'Transport' => 'Transport',
                        'Materials' => 'Materials',
                        'Equipment' => 'Equipment',
                        'Other'     => 'Other',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('$')
                    ->required()
                    ->minValue(0.01),
                Forms\Components\DatePicker::make('expense_date')
                    ->required()
                    ->default(today()),
                Forms\Components\Select::make('currency')
                    ->options(['USD' => 'USD', 'ZWL' => 'ZWL'])
                    ->default('USD'),
                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\Hidden::make('submitted_by')->default(fn () => auth()->id()),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('workOrder.reference_number')
                    ->label('Work Order')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Labour'    => 'info',
                        'Transport' => 'warning',
                        'Materials' => 'primary',
                        'Equipment' => 'danger',
                        default     => 'gray',
                    }),
                Tables\Columns\TextColumn::make('amount')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expense_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('approval_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => 'warning',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        default    => 'Pending Approval',
                    }),
                Tables\Columns\TextColumn::make('rejection_reason')
                    ->label('Rejection Reason')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->approval_status === 'pending'),
                Tables\Actions\ViewAction::make()->iconButton(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make()->schema([
                Infolists\Components\TextEntry::make('workOrder.reference_number')->label('Work Order'),
                Infolists\Components\TextEntry::make('category')->badge(),
                Infolists\Components\TextEntry::make('amount')->money('USD'),
                Infolists\Components\TextEntry::make('expense_date')->date(),
                Infolists\Components\TextEntry::make('approval_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => 'warning',
                    }),
                Infolists\Components\TextEntry::make('approvedBy.name')->label('Reviewed By')->placeholder('—'),
                Infolists\Components\TextEntry::make('description')->columnSpanFull()->placeholder('—'),
                Infolists\Components\TextEntry::make('rejection_reason')->label('Rejection Reason')->columnSpanFull()->placeholder('—')
                    ->visible(fn ($record) => $record->approval_status === 'rejected'),
            ])->columns(3),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'view'   => Pages\ViewExpense::route('/{record}'),
            'edit'   => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
