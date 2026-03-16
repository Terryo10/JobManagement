<?php

namespace App\Filament\Marketing\Resources;

use App\Filament\Marketing\Resources\TaskResource\Pages;
use App\Filament\Marketing\Resources\TaskResource\RelationManagers;
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
                Forms\Components\Select::make('work_order_id')->relationship('workOrder', 'reference_number')->searchable()->preload()->required(),
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
                ->requiresConfirmation()
                ->visible(fn ($record) => $record->claimed_by !== null)
                ->action(function ($record) {
                    $record->release();
                    \Filament\Notifications\Notification::make()->title('Task unassigned and returned to queue.')->success()->send();
                }),
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
        ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SubtasksRelationManager::class,
            RelationManagers\CommentsRelationManager::class,
            RelationManagers\TimeLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'view'   => Pages\ViewTask::route('/{record}'),
            'edit'   => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
