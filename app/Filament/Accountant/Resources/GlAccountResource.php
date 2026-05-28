<?php

namespace App\Filament\Accountant\Resources;

use App\Filament\Accountant\Resources\GlAccountResource\Pages;
use App\Models\GlAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GlAccountResource extends Resource
{
    protected static ?string $model = GlAccount::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'GL Accounts';
    protected static ?string $pluralLabel = 'GL Accounts';
    protected static ?string $modelLabel = 'GL Account';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Account Details')
                ->description('Define the GL account code and name used across requisitions.')
                ->schema([
                    Forms\Components\TextInput::make('code')
                        ->label('Account Code')
                        ->placeholder('e.g. 5010')
                        ->required()
                        ->maxLength(50)
                        ->unique(ignoreRecord: true),

                    Forms\Components\TextInput::make('name')
                        ->label('Account Name')
                        ->placeholder('e.g. Marketing Expenses')
                        ->required()
                        ->maxLength(150),

                    Forms\Components\Textarea::make('description')
                        ->label('Description (Optional)')
                        ->placeholder('Brief description of what this account covers…')
                        ->rows(2)
                        ->columnSpanFull(),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->helperText('Inactive accounts will not appear in requisition dropdowns.'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Code copied'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Account Name')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(60)
                    ->placeholder('—')
                    ->toggleable(),

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
            ->defaultSort('code')
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
            'index'  => Pages\ListGlAccounts::route('/'),
            'create' => Pages\CreateGlAccount::route('/create'),
            'edit'   => Pages\EditGlAccount::route('/{record}/edit'),
        ];
    }
}
