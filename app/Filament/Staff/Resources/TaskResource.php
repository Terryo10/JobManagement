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
        return parent::getEloquentQuery()
            ->where(fn (Builder $q) => $q
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
                    ->required(),
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
                    ->visible(fn ($record) => $record->claimed_by === null && $record->status !== 'completed')
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
                    ->visible(fn ($record) => $record->claimed_by === auth()->id() && $record->status !== 'completed')
                    ->action(function ($record) {
                        $record->release();
                        Notification::make()->title('Task released back to queue.')->success()->send();
                    }),
                Tables\Actions\EditAction::make()->label('Update')
                    ->visible(fn ($record) => $record->claimed_by === auth()->id() && $record->status !== 'completed'),
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
