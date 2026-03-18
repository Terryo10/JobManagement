<?php

namespace App\Filament\Admin\Resources\WorkOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
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
            Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
            Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull(),
            Forms\Components\Select::make('assigned_to')
                ->relationship('assignedTo', 'name')->searchable()->preload(),
            Forms\Components\Select::make('department_id')
                ->relationship('department', 'name')->searchable()->preload(),
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
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->limit(35),
                Tables\Columns\TextColumn::make('assignedTo.name')->label('Assigned To'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'pending' => 'gray', 'in_progress' => 'warning', 'completed' => 'success',
                    'blocked' => 'danger', 'cancelled' => 'info', default => 'gray',
                }),
                Tables\Columns\TextColumn::make('priority')->badge()->color(fn ($state) => match ($state) {
                    'low' => 'gray', 'normal' => 'info', 'high' => 'warning', 'urgent' => 'danger', default => 'gray',
                }),
                Tables\Columns\TextColumn::make('completion_percentage')->suffix('%')->sortable(),
                Tables\Columns\TextColumn::make('deadline')->date()->sortable(),
            ])
            ->filters([])
            ->headerActions([Tables\Actions\CreateAction::make()->mutateFormDataUsing(function (array $data): array {
                $data['created_by'] = auth()->id();
                return $data;
            })])
            ->actions([
                Tables\Actions\Action::make('documents')
                    ->label('Documents')
                    ->icon('heroicon-o-paper-clip')
                    ->color('gray')
                    ->url(fn ($record) => \App\Filament\Admin\Resources\TaskResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(),
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }
}
