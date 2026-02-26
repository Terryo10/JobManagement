<?php

namespace App\Filament\Admin\Resources\WorkOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class NotesRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';
    protected static ?string $title = 'Notes';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Textarea::make('content')->required()->rows(4)->columnSpanFull(),
            Forms\Components\Toggle::make('is_internal')->label('Internal Only')->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('content')->limit(60)->wrap(),
                Tables\Columns\TextColumn::make('user.name')->label('Author'),
                Tables\Columns\IconColumn::make('is_internal')->boolean()->label('Internal'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([Tables\Actions\CreateAction::make()->mutateFormDataUsing(function (array $data): array {
                $data['user_id'] = auth()->id();
                return $data;
            })])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }
}
