<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DeadlineEscalationResource\Pages;
use App\Models\DeadlineEscalation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DeadlineEscalationResource extends Resource
{
    protected static ?string $model = DeadlineEscalation::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('escalatable_type')->required(),
            Forms\Components\TextInput::make('escalatable_id')->numeric()->required(),
            Forms\Components\TextInput::make('escalation_level')->numeric()->default(1),
            Forms\Components\Select::make('escalated_to')->relationship('escalatedTo', 'name')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('overdue_hours_at_escalation')->numeric()->required(),
            Forms\Components\DateTimePicker::make('resolved_at'),
            Forms\Components\Textarea::make('reason')->required()->rows(4)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('escalatable_type')->label('Type'),
            Tables\Columns\TextColumn::make('escalatable_id')->label('ID'),
            Tables\Columns\TextColumn::make('escalation_level')->badge(),
            Tables\Columns\TextColumn::make('escalatedTo.name')->label('Escalated To'),
            Tables\Columns\TextColumn::make('overdue_hours_at_escalation')->label('Overdue Hours'),
            Tables\Columns\IconColumn::make('resolved_at')->boolean()->label('Resolved'),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
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
            'index'  => Pages\ListDeadlineEscalations::route('/'),
            'create' => Pages\CreateDeadlineEscalation::route('/create'),
            'edit'   => Pages\EditDeadlineEscalation::route('/{record}/edit'),
        ];
    }
}
