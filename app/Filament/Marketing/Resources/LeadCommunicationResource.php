<?php

namespace App\Filament\Marketing\Resources;

use App\Filament\Marketing\Resources\LeadCommunicationResource\Pages;
use App\Models\LeadCommunication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LeadCommunicationResource extends Resource
{
    protected static ?string $model = LeadCommunication::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'Pipeline';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Communications';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\Select::make('lead_id')->relationship('lead', 'contact_name')->searchable()->preload()->required(),
                Forms\Components\Select::make('type')->options([
                    'call' => 'Call', 'email' => 'Email', 'meeting' => 'Meeting', 'visit' => 'Visit', 'note' => 'Note',
                ])->required(),
                Forms\Components\Textarea::make('summary')->required()->rows(3)->columnSpanFull(),
                Forms\Components\TextInput::make('outcome')->maxLength(100),
                Forms\Components\Textarea::make('next_action')->rows(2)->columnSpanFull(),
                Forms\Components\DatePicker::make('next_action_date'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('lead.contact_name')->label('Lead')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('type')->badge()->color(fn ($state) => match ($state) {
                'call' => 'info', 'email' => 'primary', 'meeting' => 'success', 'visit' => 'warning', default => 'gray',
            }),
            Tables\Columns\TextColumn::make('summary')->limit(50),
            Tables\Columns\TextColumn::make('outcome')->limit(30),
            Tables\Columns\TextColumn::make('next_action_date')->date()->sortable()
                ->color(fn ($record) => $record->next_action_date && $record->next_action_date->isPast() ? 'danger' : null),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
        ])
        ->defaultSort('created_at', 'desc')
        ->filters([
            Tables\Filters\SelectFilter::make('type')->options([
                'call' => 'Call', 'email' => 'Email', 'meeting' => 'Meeting', 'visit' => 'Visit', 'note' => 'Note',
            ]),
        ])
        ->actions([Tables\Actions\EditAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLeadCommunications::route('/'),
            'create' => Pages\CreateLeadCommunication::route('/create'),
            'edit'   => Pages\EditLeadCommunication::route('/{record}/edit'),
        ];
    }
}
