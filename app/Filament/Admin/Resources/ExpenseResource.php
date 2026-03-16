<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;
    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('work_order_id')->relationship('workOrder', 'reference_number')->searchable()->preload()->required(),
            Forms\Components\Select::make('category')->options(['Labour' => 'Labour', 'Transport' => 'Transport', 'Materials' => 'Materials', 'Equipment' => 'Equipment'])->required(),
            Forms\Components\TextInput::make('amount')->numeric()->required()->prefix('USD'),
            Forms\Components\TextInput::make('currency')->default('USD')->maxLength(10),
            Forms\Components\DatePicker::make('expense_date')->required(),
            Forms\Components\Select::make('submitted_by')->relationship('submittedBy', 'name')->searchable()->preload()->required(),
            Forms\Components\Select::make('approved_by')->relationship('approvedBy', 'name')->searchable()->preload(),
            Forms\Components\Select::make('approval_status')->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])->default('pending')->required(),
            Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull(),
            Forms\Components\Textarea::make('rejection_reason')->rows(2)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('workOrder.reference_number')->label('Work Order')->sortable(),
            Tables\Columns\TextColumn::make('category')->badge(),
            Tables\Columns\TextColumn::make('amount')->money('USD')->sortable(),
            Tables\Columns\TextColumn::make('expense_date')->date()->sortable(),
            Tables\Columns\TextColumn::make('submittedBy.name')->label('Submitted By'),
            Tables\Columns\TextColumn::make('approval_status')->badge()->color(fn ($state) => match ($state) { 'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', default => 'gray' }),
        ])
        ->filters([Tables\Filters\SelectFilter::make('approval_status')->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])])
        ->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Admin\Resources\ExpenseResource\RelationManagers\DocumentsRelationManager::class,
        ];
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
