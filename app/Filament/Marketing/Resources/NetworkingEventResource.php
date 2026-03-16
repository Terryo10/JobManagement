<?php

namespace App\Filament\Marketing\Resources;

use App\Filament\Marketing\Resources\NetworkingEventResource\Pages;
use App\Models\NetworkingEvent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NetworkingEventResource extends Resource
{
    protected static ?string $model = NetworkingEvent::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Strategy';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255)->columnSpanFull(),
                Forms\Components\Select::make('type')->options([
                    'conference' => 'Conference', 'seminar' => 'Seminar', 'trade_show' => 'Trade Show',
                    'networking' => 'Networking Event', 'workshop' => 'Workshop',
                ])->default('networking')->required(),
                Forms\Components\TextInput::make('location')->maxLength(255),
                Forms\Components\DatePicker::make('start_date')->required(),
                Forms\Components\DatePicker::make('end_date'),
                Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull(),
                Forms\Components\TagsInput::make('attendees')->placeholder('Add attendee names...')->columnSpanFull(),
                Forms\Components\Textarea::make('outcomes')->rows(3)->columnSpanFull(),
                Forms\Components\TextInput::make('leads_generated')->numeric()->default(0),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('type')->badge()->color(fn ($state) => match ($state) {
                'conference' => 'primary', 'seminar' => 'info', 'trade_show' => 'warning', 'networking' => 'success', 'workshop' => 'gray', default => 'gray',
            }),
            Tables\Columns\TextColumn::make('start_date')->date()->sortable(),
            Tables\Columns\TextColumn::make('location')->limit(30),
            Tables\Columns\TextColumn::make('leads_generated')->sortable(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('type')->options([
                'conference' => 'Conference', 'seminar' => 'Seminar', 'trade_show' => 'Trade Show',
                'networking' => 'Networking Event', 'workshop' => 'Workshop',
            ]),
        ])
        ->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Marketing\Resources\NetworkingEventResource\RelationManagers\DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListNetworkingEvents::route('/'),
            'create' => Pages\CreateNetworkingEvent::route('/create'),
            'view'   => Pages\ViewNetworkingEvent::route('/{record}'),
            'edit'   => Pages\EditNetworkingEvent::route('/{record}/edit'),
        ];
    }
}
