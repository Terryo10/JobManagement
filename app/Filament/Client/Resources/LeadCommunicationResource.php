<?php

namespace App\Filament\Client\Resources;

use App\Filament\Client\Resources\LeadCommunicationResource\Pages;
use App\Models\LeadCommunication;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Shared\Concerns\EnforcesAdminDelete;

class LeadCommunicationResource extends Resource
{
    use EnforcesAdminDelete;
    protected static ?string $model = LeadCommunication::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Communications';
    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('lead.client', fn ($q) => $q->where('email', auth()->user()?->email));
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('type')->badge(),
            Tables\Columns\TextColumn::make('summary')->limit(60),
            Tables\Columns\TextColumn::make('outcome'),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->label('Date'),
        ])
        ->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeadCommunications::route('/'),
            'view'  => Pages\ViewLeadCommunication::route('/{record}'),
        ];
    }
}
