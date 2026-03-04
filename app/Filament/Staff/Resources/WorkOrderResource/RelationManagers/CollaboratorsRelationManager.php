<?php

namespace App\Filament\Staff\Resources\WorkOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CollaboratorsRelationManager extends RelationManager
{
    protected static string $relationship = 'collaborators';
    protected static ?string $title = 'Collaborators';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email'),
                Tables\Columns\TextColumn::make('pivot.role')
                    ->label('Role')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'lead' => 'warning',
                        'supervisor' => 'danger',
                        default => 'info',
                    }),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Add Collaborator')
                    ->recordTitleAttribute('name')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'email'])
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()->label('Staff Member'),
                        Forms\Components\Select::make('role')
                            ->options([
                                'collaborator' => 'Collaborator',
                                'lead' => 'Team Lead',
                                'supervisor' => 'Supervisor',
                            ])
                            ->default('collaborator')
                            ->required(),
                    ]),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()->label('Remove'),
            ]);
    }
}
