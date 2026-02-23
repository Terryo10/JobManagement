<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\NotificationPreferenceResource\Pages;
use App\Models\NotificationPreference;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationPreferenceResource extends Resource
{
    protected static ?string $model = NotificationPreference::class;
    protected static ?string $navigationIcon = 'heroicon-o-bell';
    protected static ?string $navigationGroup = 'Notifications';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')->relationship('user', 'name')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('notification_type')->required()->maxLength(255),
            Forms\Components\Toggle::make('channel_database')->label('In-App')->default(true),
            Forms\Components\Toggle::make('channel_mail')->label('Email')->default(true),
            Forms\Components\Toggle::make('channel_sms')->label('SMS')->default(false),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('user.name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('notification_type')->searchable(),
            Tables\Columns\IconColumn::make('channel_database')->boolean()->label('In-App'),
            Tables\Columns\IconColumn::make('channel_mail')->boolean()->label('Email'),
            Tables\Columns\IconColumn::make('channel_sms')->boolean()->label('SMS'),
        ])
        ->actions([Tables\Actions\EditAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListNotificationPreferences::route('/'),
            'create' => Pages\CreateNotificationPreference::route('/create'),
            'edit'   => Pages\EditNotificationPreference::route('/{record}/edit'),
        ];
    }
}
