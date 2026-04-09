<?php

namespace App\Filament\Staff\Resources\WorkOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TimeLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'taskTimeLogs';

    protected static ?string $title = 'Time Logs';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('task_id')
                ->label('Task')
                ->options(fn () => $this->getOwnerRecord()
                    ->tasks()
                    ->orderBy('title')
                    ->pluck('title', 'id')
                )
                ->required()
                ->searchable(),
            Forms\Components\Hidden::make('user_id')
                ->default(fn () => auth()->id()),
            Forms\Components\DateTimePicker::make('started_at')
                ->label('Start Time')
                ->required(),
            Forms\Components\DateTimePicker::make('ended_at')
                ->label('End Time')
                ->after('started_at'),
            Forms\Components\TextInput::make('duration_minutes')
                ->label('Duration (minutes)')
                ->numeric()
                ->minValue(1)
                ->helperText('Leave blank to auto-calculate from start/end times.'),
            Forms\Components\Textarea::make('notes')
                ->rows(2)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('task.title')
                    ->label('Task')
                    ->limit(35),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Logged By'),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('Start')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ended_at')
                    ->label('End')
                    ->dateTime('d M Y, H:i')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => $state ? $state . ' min' : '—'),
            ])
            ->defaultSort('started_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Log Time'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                \App\Filament\Shared\Actions\RequestDeletionTableAction::make(),
            ])
            ->emptyStateHeading('No time logged yet')
            ->emptyStateDescription('Track the hours you spend on tasks for this job.')
            ->emptyStateIcon('heroicon-o-clock');
    }
}
