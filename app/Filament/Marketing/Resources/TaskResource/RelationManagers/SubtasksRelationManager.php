<?php

namespace App\Filament\Marketing\Resources\TaskResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SubtasksRelationManager extends RelationManager
{
    protected static string $relationship = 'subtasks';
    protected static ?string $title = 'Subtasks';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
            Forms\Components\Textarea::make('description')->rows(2)->columnSpanFull(),
            Forms\Components\Select::make('assigned_to')
                ->relationship('assignedTo', 'name')->searchable()->preload(),
            Forms\Components\Select::make('status')
                ->options(['pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'blocked' => 'Blocked'])
                ->default('pending')->required(),
            Forms\Components\Select::make('priority')
                ->options(['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent'])
                ->default('normal')->required(),
            Forms\Components\TextInput::make('estimated_hours')->numeric()->suffix('hrs'),
            Forms\Components\DatePicker::make('deadline'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('assignedTo.name')->label('Assigned'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'pending' => 'gray', 'in_progress' => 'warning', 'completed' => 'success',
                    'blocked' => 'danger', default => 'gray',
                }),
                Tables\Columns\TextColumn::make('completion_percentage')->suffix('%'),
                Tables\Columns\TextColumn::make('deadline')->date(),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()->mutateFormDataUsing(function (array $data): array {
                $data['work_order_id'] = $this->getOwnerRecord()->work_order_id;
                $data['created_by'] = auth()->id();
                return $data;
            })])
            ->actions([Tables\Actions\EditAction::make(), \App\Filament\Shared\Actions\RequestDeletionTableAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }
}
