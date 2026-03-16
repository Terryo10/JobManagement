<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LeadResource\Pages;
use App\Models\Lead;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;
    protected static ?string $navigationIcon = 'heroicon-o-funnel';
    protected static ?string $navigationGroup = 'CRM';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\Select::make('client_id')->relationship('client', 'company_name')->searchable()->preload(),
                Forms\Components\TextInput::make('contact_name')->required()->maxLength(255),
                Forms\Components\TextInput::make('contact_email')->email(),
                Forms\Components\TextInput::make('contact_phone')->maxLength(30),
                Forms\Components\TextInput::make('company_name')->maxLength(255),
                Forms\Components\TextInput::make('source')->maxLength(100),
                Forms\Components\Select::make('status')->options(['new' => 'New', 'in_progress' => 'In Progress', 'converted' => 'Converted', 'lost' => 'Lost'])->default('new')->required(),
                Forms\Components\Select::make('assigned_to')->relationship('assignedTo', 'name')->searchable()->preload(),
                Forms\Components\DatePicker::make('follow_up_date'),
                Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
                Forms\Components\Textarea::make('lost_reason')->rows(2)->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('contact_name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('company_name')->searchable(),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) { 'new' => 'info', 'in_progress' => 'warning', 'converted' => 'success', 'lost' => 'danger', default => 'gray' }),
            Tables\Columns\TextColumn::make('source'),
            Tables\Columns\TextColumn::make('assignedTo.name')->label('Assigned To'),
            Tables\Columns\TextColumn::make('follow_up_date')->date()->sortable(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')->options(['new' => 'New', 'in_progress' => 'In Progress', 'converted' => 'Converted', 'lost' => 'Lost']),
            Tables\Filters\TrashedFilter::make(),
        ])
        ->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Admin\Resources\LeadResource\RelationManagers\DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLeads::route('/'),
            'create' => Pages\CreateLead::route('/create'),
            'view'   => Pages\ViewLead::route('/{record}'),
            'edit'   => Pages\EditLead::route('/{record}/edit'),
        ];
    }
}
