<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\TaskResource\Pages;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Shared\Concerns\EnforcesAdminDelete;

class TaskResource extends Resource
{
    use EnforcesAdminDelete;
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationLabel = 'Tasks';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        // Global queue: show tasks I've claimed + unclaimed tasks available for picking
        // Workshop Managers and Admins can see all tasks to assign them
        $query = parent::getEloquentQuery();

        if (auth()->check() && auth()->user()->hasRole(['super_admin', 'workshop_manager'])) {
            return $query;
        }

        return $query->where(fn (Builder $q) => $q
                ->where('claimed_by', auth()->id())
                ->orWhereNull('claimed_by')
            );
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Task Details')->schema([
                Forms\Components\Select::make('work_order_id')
                    ->relationship('workOrder', 'reference_number')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->reference_number} – {$record->title}")
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
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
                    ->options(['pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'blocked' => 'Blocked'])
                    ->default('pending')
                    ->required(),
                Forms\Components\Select::make('priority')
                    ->options(['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent'])
                    ->default('normal')
                    ->required(),
                Forms\Components\TextInput::make('completion_percentage')->numeric()->suffix('%')->minValue(0)->maxValue(100)->default(0),
                Forms\Components\TextInput::make('actual_hours')->numeric()->suffix('hrs')->label('Hours Worked')->default(0),
                Forms\Components\DatePicker::make('deadline'),
                Forms\Components\RichEditor::make('description')
                    ->toolbarButtons(['bold', 'italic', 'underline', 'bulletList', 'orderedList', 'link', 'undo', 'redo'])
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->searchable()->limit(45),
            Tables\Columns\TextColumn::make('workOrder.reference_number')->label('Job Card'),
            Tables\Columns\TextColumn::make('assignedTo.name')->label('Assigned To')
                ->placeholder('— Unassigned —')
                ->badge()
                ->color(fn ($state) => $state ? 'success' : 'gray'),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                'pending' => 'gray', 'in_progress' => 'warning', 'completed' => 'success', 'blocked' => 'danger', default => 'gray',
            }),
            Tables\Columns\TextColumn::make('priority')->badge()->color(fn ($state) => match ($state) {
                'low' => 'gray', 'normal' => 'info', 'high' => 'warning', 'urgent' => 'danger', default => 'gray',
            }),
            Tables\Columns\TextColumn::make('completion_percentage')->suffix('%')->sortable(),
            Tables\Columns\TextColumn::make('deadline')->date()->sortable()
                ->color(fn ($record) => $record->deadline && $record->deadline->isPast() ? 'danger' : null),
            Tables\Columns\TextColumn::make('fieldWorkers.name')
                ->label('Field Workers')
                ->badge()
                ->color('warning')
                ->formatStateUsing(fn ($state) => $state . ' [FW]')
                ->separator(',')
                ->placeholder('None'),
        ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'blocked' => 'Blocked']),
                Tables\Filters\SelectFilter::make('queue')
                    ->options(['available' => 'Available to Claim', 'mine' => 'My Tasks'])
                    ->query(function (Builder $query, array $data) {
                        return match ($data['value'] ?? null) {
                            'available' => $query->whereNull('claimed_by'),
                            'mine' => $query->where('claimed_by', auth()->id()),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                 Tables\Actions\Action::make('claim')
                    ->label('Claim Task')
                    ->icon('heroicon-o-hand-raised')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Claim this task?')
                    ->modalDescription('You will be assigned to this task and it will move to "In Progress".')
                    ->visible(fn ($record) => $record->work_order_id !== null && $record->claimed_by === null && $record->status !== 'completed')
                    ->action(function ($record) {
                        $success = $record->claim(auth()->user());
                        if ($success) {
                            Notification::make()->title('Task claimed!')->success()->send();
                        } else {
                            Notification::make()->title('This task was already claimed by someone else.')->danger()->send();
                        }
                    }),
                 Tables\Actions\Action::make('release')
                    ->label('Release')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Release this task?')
                    ->modalDescription('This task will go back to the queue for others to claim.')
                    ->visible(fn ($record) => $record->work_order_id !== null && $record->claimed_by === auth()->id() && $record->status !== 'completed')
                    ->action(function ($record) {
                        $record->release();
                        Notification::make()->title('Task released back to queue.')->success()->send();
                    }),
                Tables\Actions\EditAction::make()->label('Update')
                    ->visible(fn ($record) => ($record->work_order_id === null || $record->claimed_by === auth()->id()) && $record->status !== 'completed'),
                Tables\Actions\Action::make('assign_field_workers')
                    ->label('Field Workers')
                    ->icon('heroicon-o-identification')
                    ->color('info')
                    ->modalHeading('Assign Field Workers & Instructions')
                    ->modalDescription('Assign field workers and specify exactly what they need to do. They will receive an email or WhatsApp notification.')
                    ->form(fn ($record) => [
                        Forms\Components\Repeater::make('assignments')
                            ->label('')
                            ->schema([
                            Forms\Components\Select::make('field_worker_id')
                                ->label('Field Worker')
                                ->options(\App\Models\FieldWorker::orderBy('name')->pluck('name', 'id'))
                                ->required()
                                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                ->columnSpan(2),
                            Forms\Components\DateTimePicker::make('deadline')
                                ->label('Custom Deadline')
                                ->nullable()
                                ->columnSpan(2),
                            Forms\Components\Textarea::make('notes')
                                ->label('What must they do?')
                                ->required()
                                ->columnSpan(4),
                        ])
                        ->columns(4)
                        ->default(function () use ($record) {
                            return $record->fieldWorkers->map(function ($worker) {
                                return [
                                    'field_worker_id' => $worker->id,
                                    'notes' => $worker->pivot->notes,
                                    'deadline' => $worker->pivot->deadline,
                                ];
                            })->toArray();
                        })
                            ->addActionLabel('Add Worker Assignment'),
                    ])
                    ->visible(fn () => auth()->check() && auth()->user()->hasRole(['super_admin', 'workshop_manager']))
                    ->action(function ($record, array $data) {
                        $before = $record->fieldWorkers()->pluck('field_workers.id');
                        
                        $syncData = [];
                        $newAssignments = [];

                        foreach ($data['assignments'] ?? [] as $assignment) {
                            $workerId = $assignment['field_worker_id'];
                            $notes = $assignment['notes'];
                            $deadline = $assignment['deadline'] ?? null;

                            $syncData[$workerId] = [
                                'notes' => $notes,
                                'deadline' => $deadline,
                            ];

                            if (! $before->contains($workerId)) {
                                $syncData[$workerId]['assigned_by'] = auth()->id();
                                $syncData[$workerId]['assigned_at'] = now();
                                $newAssignments[] = [
                                    'id' => $workerId,
                                    'notes' => $notes,
                                    'deadline' => $deadline,
                                ];
                            }
                        }

                        $record->fieldWorkers()->sync($syncData);

                        // Notify only newly added workers
                        foreach ($newAssignments as $newWorker) {
                            \App\Jobs\SendFieldWorkerNotificationJob::dispatch($newWorker['id'], $record->id, $newWorker['notes'], $newWorker['deadline'])
                                ->onQueue('notifications');
                        }

                        $count = count($syncData);
                        \Filament\Notifications\Notification::make()
                            ->title("Field workers updated ({$count} assigned).")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('update_fw_status')
                    ->label('Update FW Status')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->form(fn ($record) => [
                        Forms\Components\CheckboxList::make('completed_workers')
                            ->label('Mark as Completed')
                            ->options($record->fieldWorkers->pluck('name', 'id'))
                            ->default($record->fieldWorkers->whereNotNull('pivot.completed_at')->pluck('id')->toArray())
                            ->helperText('Check the field workers who have completed their instructions.')
                    ])
                    ->visible(fn ($record) => auth()->check() && auth()->user()->hasRole(['super_admin', 'workshop_manager']) && $record->fieldWorkers->isNotEmpty())
                    ->action(function ($record, array $data) {
                        $completedIds = $data['completed_workers'] ?? [];
                        
                        foreach ($record->fieldWorkers as $worker) {
                            $isCompleted = in_array($worker->id, $completedIds);
                            
                            if ($isCompleted && ! $worker->pivot->completed_at) {
                                $record->fieldWorkers()->updateExistingPivot($worker->id, ['completed_at' => now()]);
                            } elseif (! $isCompleted && $worker->pivot->completed_at) {
                                $record->fieldWorkers()->updateExistingPivot($worker->id, ['completed_at' => null]);
                            }
                        }
                        
                        \Filament\Notifications\Notification::make()
                            ->title("Field worker statuses updated.")
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('deadline');
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Staff\Resources\TaskResource\RelationManagers\SubtasksRelationManager::class,
            \App\Filament\Staff\Resources\TaskResource\RelationManagers\CommentsRelationManager::class,
            \App\Filament\Staff\Resources\TaskResource\RelationManagers\TimeLogsRelationManager::class,
            \App\Filament\Staff\Resources\TaskResource\RelationManagers\DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'view' => Pages\ViewTask::route('/{record}'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
