<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ClientResource\Pages;
use App\Filament\Admin\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'CRM';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('company_name')->required()->maxLength(255),
                Forms\Components\TextInput::make('contact_person')->required()->maxLength(255),
                Forms\Components\TextInput::make('email')->email(),
                Forms\Components\TextInput::make('phone')->maxLength(30),
                Forms\Components\TextInput::make('city')->maxLength(100),
                Forms\Components\Toggle::make('is_active')->default(true),
                Forms\Components\Textarea::make('address')->rows(2)->columnSpanFull(),
                Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('company_name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('contact_person')->searchable(),
            Tables\Columns\TextColumn::make('email')->searchable(),
            Tables\Columns\TextColumn::make('phone'),
            Tables\Columns\TextColumn::make('city'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
            Tables\Columns\TextColumn::make('createdBy.name')->label('Created By')->default('—')->toggleable(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            Tables\Filters\TernaryFilter::make('is_active'),
            Tables\Filters\TrashedFilter::make(),
        ])
        ->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make(), Tables\Actions\ForceDeleteBulkAction::make(), Tables\Actions\RestoreBulkAction::make()])]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Client Details')->schema([
                Infolists\Components\TextEntry::make('company_name'),
                Infolists\Components\TextEntry::make('contact_person'),
                Infolists\Components\TextEntry::make('email'),
                Infolists\Components\TextEntry::make('phone'),
                Infolists\Components\TextEntry::make('city'),
                Infolists\Components\IconEntry::make('is_active')->boolean(),
                Infolists\Components\TextEntry::make('address')->columnSpanFull(),
                Infolists\Components\TextEntry::make('notes')->columnSpanFull(),
            ])->columns(3),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\LeadsRelationManager::class,
            RelationManagers\WorkOrdersRelationManager::class,
            RelationManagers\InvoicesRelationManager::class,
            \App\Filament\Admin\Resources\ClientResource\RelationManagers\DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'view'   => Pages\ViewClient::route('/{record}'),
            'edit'   => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
