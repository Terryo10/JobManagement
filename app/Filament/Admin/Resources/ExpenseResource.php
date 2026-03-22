<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;
    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationLabel = 'Expenses';
    protected static ?string $modelLabel = 'Expense';
    protected static ?string $pluralModelLabel = 'Expenses';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 5;

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
                Forms\Components\Select::make('submitted_by')
                    ->relationship('submittedBy', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Submitted By'),
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
                Tables\Columns\TextColumn::make('submittedBy.name')
                    ->label('Submitted By')
                    ->searchable(),
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
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('approval_status')
                    ->label('Status')
                    ->options([
                        'pending'  => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'Labour'    => 'Labour',
                        'Transport' => 'Transport',
                        'Materials' => 'Materials',
                        'Equipment' => 'Equipment',
                        'Other'     => 'Other',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Approve Expense')
                    ->modalDescription('The staff member will be notified that their expense has been approved.')
                    ->visible(fn ($record) => $record->approval_status === 'pending')
                    ->action(function ($record) {
                        $record->update([
                            'approval_status' => 'approved',
                            'approved_by'     => auth()->id(),
                        ]);
                        Notification::make()
                            ->title('Expense approved. Staff member notified.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->button()
                    ->modalHeading('Reject Expense')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Reason for Rejection')
                            ->required()
                            ->rows(3),
                    ])
                    ->visible(fn ($record) => $record->approval_status === 'pending')
                    ->action(function ($record, array $data) {
                        $record->update([
                            'approval_status'  => 'rejected',
                            'approved_by'      => auth()->id(),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        Notification::make()
                            ->title('Expense rejected. Staff member notified.')
                            ->warning()
                            ->send();
                    }),
                Tables\Actions\ViewAction::make()->iconButton(),
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make()->schema([
                Infolists\Components\TextEntry::make('workOrder.reference_number')->label('Work Order'),
                Infolists\Components\TextEntry::make('submittedBy.name')->label('Submitted By'),
                Infolists\Components\TextEntry::make('category')->badge(),
                Infolists\Components\TextEntry::make('amount')->money('USD'),
                Infolists\Components\TextEntry::make('expense_date')->date(),
                Infolists\Components\TextEntry::make('currency'),
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
                Infolists\Components\TextEntry::make('rejection_reason')
                    ->label('Rejection Reason')
                    ->columnSpanFull()
                    ->placeholder('—')
                    ->visible(fn ($record) => $record->approval_status === 'rejected'),
            ])->columns(4),
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
