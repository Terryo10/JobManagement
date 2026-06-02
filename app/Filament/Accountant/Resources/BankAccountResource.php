<?php

namespace App\Filament\Accountant\Resources;

use App\Filament\Accountant\Resources\BankAccountResource\Pages;
use App\Models\BankAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BankAccountResource extends Resource
{
    protected static ?string $model = BankAccount::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'Bank Accounts';
    protected static ?string $pluralLabel = 'Bank Accounts';
    protected static ?string $modelLabel = 'Bank Account';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Bank Account Details')
                ->description('Define the bank account details that appear on invoices, quotations and proposals.')
                ->schema([
                    Forms\Components\TextInput::make('account_name')
                        ->label('Account Name')
                        ->placeholder('e.g. Household Brands (Pvt) Ltd')
                        ->required()
                        ->maxLength(150),

                    Forms\Components\TextInput::make('bank_name')
                        ->label('Bank Name')
                        ->placeholder('e.g. NMB Bank')
                        ->required()
                        ->maxLength(150),

                    Forms\Components\TextInput::make('branch')
                        ->label('Branch')
                        ->placeholder('e.g. Eastgate')
                        ->maxLength(100),

                    Forms\Components\TextInput::make('account_number')
                        ->label('Account Number')
                        ->placeholder('e.g. 100040041620')
                        ->required()
                        ->maxLength(50),

                    Forms\Components\Toggle::make('is_default')
                        ->label('Default Account')
                        ->default(false)
                        ->helperText('The default account will be pre-selected when creating new documents.'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->helperText('Inactive accounts will not appear in dropdowns.'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('account_name')
                    ->label('Account Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('bank_name')
                    ->label('Bank')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('branch')
                    ->label('Branch')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('account_number')
                    ->label('Acc No.')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Account number copied'),

                Tables\Columns\IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Added')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('account_name')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->placeholder('All'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBankAccounts::route('/'),
            'create' => Pages\CreateBankAccount::route('/create'),
            'edit'   => Pages\EditBankAccount::route('/{record}/edit'),
        ];
    }
}
