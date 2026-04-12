<?php

namespace App\Filament\Staff\Resources\WorkOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';
    protected static ?string $title = 'Tasks';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            Forms\Components\Select::make('assigned_to')
                ->label('Assign To')
                ->relationship(
                    'assignedTo',
                    'name',
                    fn ($query) => $query->whereHas(
                        'roles',
                        fn ($q) => $q->whereNotIn('name', ['client'])
                    )->orderBy('name')
                )
                ->searchable()
                ->preload()
                ->nullable()
                ->placeholder('— Unassigned —'),
            Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'Pending',
                    'in_progress' => 'In Progress',
                    'completed' => 'Completed',
                    'blocked' => 'Blocked',
                ])
                ->default('pending')
                ->required(),
            Forms\Components\Select::make('priority')
                ->options([
                    'low' => 'Low',
                    'normal' => 'Normal',
                    'high' => 'High',
                    'urgent' => 'Urgent',
                ])
                ->default('normal')
                ->required(),
            Forms\Components\TextInput::make('completion_percentage')
                ->numeric()
                ->suffix('%')
                ->minValue(0)
                ->maxValue(100)
                ->default(0),
            Forms\Components\TextInput::make('estimated_hours')
                ->numeric()
                ->suffix('hrs')
                ->label('Estimated Hours'),
            Forms\Components\DatePicker::make('start_date'),
            Forms\Components\DatePicker::make('deadline'),
            Forms\Components\RichEditor::make('description')
                ->toolbarButtons(['bold', 'italic', 'underline', 'bulletList', 'orderedList', 'link', 'undo', 'redo'])
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Assigned To')
                    ->placeholder('— Unassigned —')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'pending' => 'gray',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'blocked' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'low' => 'gray',
                        'normal' => 'info',
                        'high' => 'warning',
                        'urgent' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('completion_percentage')
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('deadline')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => $record->deadline && $record->deadline->isPast() ? 'danger' : null),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Task')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['created_by'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('assign')
                    ->label('Assign')
                    ->icon('heroicon-o-user-plus')
                    ->color('info')
                    ->form([
                        Forms\Components\Select::make('assigned_to')
                            ->label('Assign To')
                            ->options(fn () => \App\Models\User::whereHas(
                                'roles',
                                fn ($q) => $q->whereNotIn('name', ['client'])
                            )->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->nullable()
                            ->placeholder('— Remove assignment —'),
                    ])
                    ->fillForm(fn ($record) => ['assigned_to' => $record->assigned_to])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'assigned_to' => $data['assigned_to'] ?: null,
                        ]);
                        $name = $data['assigned_to']
                            ? \App\Models\User::find($data['assigned_to'])?->name
                            : 'nobody';
                        Notification::make()->title("Task assigned to {$name}.")->success()->send();
                    }),
                Tables\Actions\Action::make('claim')
                    ->label('Claim')
                    ->icon('heroicon-o-hand-raised')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->claimed_by === null && $record->status !== 'completed')
                    ->action(function ($record) {
                        $success = $record->claim(auth()->user());
                        if ($success) {
                            Notification::make()->title('Task claimed!')->success()->send();
                        } else {
                            Notification::make()->title('Already claimed by someone else.')->danger()->send();
                        }
                    }),
                Tables\Actions\Action::make('release')
                    ->label('Release')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->claimed_by === auth()->id() && $record->status !== 'completed')
                    ->action(function ($record) {
                        $record->release();
                        Notification::make()->title('Task released back to queue.')->success()->send();
                    }),
                Tables\Actions\Action::make('documents')
                    ->label('Documents')
                    ->icon('heroicon-o-paper-clip')
                    ->color('gray')
                    ->url(fn ($record) => \App\Filament\Staff\Resources\TaskResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make()
                    ->label('Update')
                    ->visible(fn ($record) => $record->status !== 'completed'),
            ])
            ->defaultSort('deadline');
    }
}
