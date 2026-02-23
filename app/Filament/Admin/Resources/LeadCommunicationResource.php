<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LeadCommunicationResource\Pages;
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
    protected static ?string $navigationGroup = 'CRM';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('lead_id')->relationship('lead', 'contact_name')->searchable()->preload()->required(),
            Forms\Components\Select::make('user_id')->relationship('user', 'name')->searchable()->preload()->required(),
            Forms\Components\Select::make('type')->options(['call' => 'Call', 'email' => 'Email', 'meeting' => 'Meeting', 'visit' => 'Visit', 'note' => 'Note'])->required(),
            Forms\Components\TextInput::make('outcome')->maxLength(100),
            Forms\Components\DatePicker::make('next_action_date'),
            Forms\Components\Textarea::make('summary')->required()->rows(4)->columnSpanFull(),
            Forms\Components\Textarea::make('next_action')->rows(2)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('lead.contact_name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('user.name')->label('Logged By'),
            Tables\Columns\TextColumn::make('type')->badge(),
            Tables\Columns\TextColumn::make('outcome'),
            Tables\Columns\TextColumn::make('next_action_date')->date()->sortable(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([Tables\Filters\SelectFilter::make('type')->options(['call' => 'Call', 'email' => 'Email', 'meeting' => 'Meeting', 'visit' => 'Visit', 'note' => 'Note'])])
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
            'index'  => Pages\ListLeadCommunications::route('/'),
            'create' => Pages\CreateLeadCommunication::route('/create'),
            'edit'   => Pages\EditLeadCommunication::route('/{record}/edit'),
        ];
    }
}
