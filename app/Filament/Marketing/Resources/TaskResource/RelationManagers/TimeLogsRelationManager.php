<?php

namespace App\Filament\Marketing\Resources\TaskResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TimeLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'timeLogs';
    protected static ?string $title = 'Time Logs';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DateTimePicker::make('started_at')->required(),
            Forms\Components\DateTimePicker::make('ended_at'),
            Forms\Components\TextInput::make('hours')->numeric()->required()->suffix('hrs'),
            Forms\Components\Textarea::make('description')->rows(2)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Staff'),
                Tables\Columns\TextColumn::make('hours')->numeric(2)->suffix(' hrs')->sortable(),
                Tables\Columns\TextColumn::make('description')->limit(40),
                Tables\Columns\TextColumn::make('started_at')->dateTime()->sortable(),
            ])
            ->defaultSort('started_at', 'desc')
            ->headerActions([Tables\Actions\CreateAction::make()->mutateFormDataUsing(function (array $data): array {
                $data['user_id'] = auth()->id();
                return $data;
            })])
            ->actions([Tables\Actions\EditAction::make(), \App\Filament\Shared\Actions\RequestDeletionTableAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }
}
