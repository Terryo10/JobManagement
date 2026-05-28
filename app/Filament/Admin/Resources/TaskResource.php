<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Actions\SendMessageAction;
use App\Filament\Admin\Resources\TaskResource\Pages;
use App\Filament\Admin\Resources\TaskResource\RelationManagers;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Tasks';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\Select::make('work_order_id')
                    ->relationship('workOrder', 'reference_number')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->reference_number} – {$record->title}")
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                Forms\Components\Select::make('assigned_to')->relationship('assignedTo', 'name')->searchable()->preload(),
                Forms\Components\Select::make('department_id')->relationship('department', 'name')->searchable()->preload(),
                Forms\Components\Select::make('status')
                    ->options(['pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'blocked' => 'Blocked', 'cancelled' => 'Cancelled'])
                    ->default('pending')->required(),
                Forms\Components\Select::make('priority')
                    ->options(['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent'])
                    ->default('normal')->required(),
                Forms\Components\TextInput::make('estimated_hours')->numeric()->suffix('hrs'),
                Forms\Components\TextInput::make('completion_percentage')->numeric()->suffix('%')->default(0)->minValue(0)->maxValue(100),
                Forms\Components\DatePicker::make('start_date'),
                Forms\Components\DatePicker::make('deadline'),
                Forms\Components\Textarea::make('description')->rows(4)->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Field Workers')
                ->description('Use the "Field Workers" action button on the task table to assign and notify field workers.')
                ->icon('heroicon-o-identification')
                ->schema([
                    Forms\Components\Placeholder::make('info')
                        ->label('')
                        ->content('Field worker assignments are managed via the Actions menu on the task list or view page. This ensures that SMS and WhatsApp notifications are properly dispatched to the workers.'),
                ])
                ->collapsible()
                ->collapsed(fn ($record) => $record === null),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->searchable()->limit(50)->sortable(),
            Tables\Columns\TextColumn::make('workOrder.reference_number')->label('Job Card'),
            Tables\Columns\TextColumn::make('claimedBy.name')->label('Claimed By')
                ->placeholder('Unclaimed')
                ->badge()
                ->color(fn ($state) => $state ? 'success' : 'gray'),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                'pending' => 'gray', 'in_progress' => 'warning', 'completed' => 'success',
                'blocked' => 'danger', 'cancelled' => 'gray', default => 'gray',
            }),
            Tables\Columns\TextColumn::make('priority')->badge()->color(fn ($state) => match ($state) {
                'low' => 'gray', 'normal' => 'info', 'high' => 'warning', 'urgent' => 'danger', default => 'gray',
            }),
            Tables\Columns\TextColumn::make('completion_percentage')->suffix('%')->sortable(),
            Tables\Columns\TextColumn::make('deadline')->date()->sortable(),
            Tables\Columns\TextColumn::make('fieldWorkers.name')
                ->label('Field Workers')
                ->badge()
                ->color('warning')
                ->formatStateUsing(fn ($state) => $state.' [Field Worker]')
                ->separator(',')
                ->placeholder('None'),
        ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(['pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'blocked' => 'Blocked', 'cancelled' => 'Cancelled']),
                Tables\Filters\SelectFilter::make('priority')->options(['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent']),
                Tables\Filters\SelectFilter::make('claimed')
                    ->options(['claimed' => 'Claimed', 'unclaimed' => 'Unclaimed'])
                    ->query(function ($query, array $data) {
                        return match ($data['value'] ?? null) {
                            'claimed' => $query->whereNotNull('claimed_by'),
                            'unclaimed' => $query->whereNull('claimed_by'),
                            default => $query,
                        };
                    }),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('reassign')
                    ->label('Reassign')
                    ->icon('heroicon-o-user-plus')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('user_id')
                            ->label('Assign to')
                            ->relationship('claimedBy', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'claimed_by' => $data['user_id'],
                            'claimed_at' => now(),
                            'assigned_to' => $data['user_id'],
                            'status' => 'in_progress',
                        ]);
                        \Filament\Notifications\Notification::make()->title('Task reassigned.')->success()->send();
                    }),
                Tables\Actions\Action::make('unassign')
                    ->label('Unassign')
                    ->icon('heroicon-o-user-minus')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Reason for unassignment')
                            ->placeholder('Optional reason...'),
                    ])
                    ->visible(fn ($record) => $record->claimed_by !== null)
                    ->action(function ($record, array $data) {
                        $record->unassignmentReason = $data['reason'] ?? null;
                        $record->release();
                        \Filament\Notifications\Notification::make()->title('Task unassigned and returned to queue.')->success()->send();
                    }),
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
                            ->helperText('Check the field workers who have completed their instructions.'),
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
                            ->title('Field worker statuses updated.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make()->label('Update'),
                SendMessageAction::make('send_message_task')
                    ->withRecordUrl(fn ($record) => url('/admin/tasks/'.$record->getKey())),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Task Details')->schema([
                Infolists\Components\TextEntry::make('title')->columnSpanFull(),
                Infolists\Components\TextEntry::make('workOrder.reference_number')->label('Job Card'),
                Infolists\Components\TextEntry::make('assignedTo.name')->label('Assigned To'),
                Infolists\Components\TextEntry::make('department.name'),
                Infolists\Components\TextEntry::make('status')->badge(),
                Infolists\Components\TextEntry::make('priority')->badge(),
                Infolists\Components\TextEntry::make('completion_percentage')->suffix('%'),
                Infolists\Components\TextEntry::make('estimated_hours')->suffix(' hrs'),
                Infolists\Components\TextEntry::make('actual_hours')->suffix(' hrs'),
                Infolists\Components\TextEntry::make('start_date')->date(),
                Infolists\Components\TextEntry::make('deadline')->date(),
                Infolists\Components\TextEntry::make('description')->columnSpanFull(),
            ])->columns(3),
            Infolists\Components\Section::make('Field Workers')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('fieldWorkers')
                        ->label('')
                        ->schema([
                            Infolists\Components\TextEntry::make('name')->weight('bold'),
                            Infolists\Components\TextEntry::make('type')->badge()
                                ->color(fn ($state) => match ($state) {
                                    'Internal' => 'success',
                                    'External' => 'warning',
                                    default => 'gray',
                                }),
                            Infolists\Components\TextEntry::make('phone_number')->label('Phone')->placeholder('—')->copyable(),
                            Infolists\Components\IconEntry::make('pivot.completed_at')
                                ->label('Status')
                                ->icon(fn ($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-clock')
                                ->color(fn ($state) => $state ? 'success' : 'warning')
                                ->tooltip(fn ($state) => $state ? 'Completed at '.\Carbon\Carbon::parse($state)->format('d M Y H:i') : 'Pending'),
                            Infolists\Components\TextEntry::make('pivot.deadline')
                                ->label('Deadline')
                                ->dateTime('d M Y H:i')
                                ->placeholder('—'),
                            Infolists\Components\TextEntry::make('pivot.notes')->label('Notes')->placeholder('—')->columnSpanFull(),
                        ])
                        ->columns(5)
                        ->columnSpanFull(),
                ])
                ->collapsible()
                ->hidden(fn ($record) => $record->fieldWorkers->isEmpty()),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SubtasksRelationManager::class,
            RelationManagers\CommentsRelationManager::class,
            RelationManagers\TimeLogsRelationManager::class,
            RelationManagers\DocumentsRelationManager::class,
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
