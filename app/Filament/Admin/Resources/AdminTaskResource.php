<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AdminTaskResource\Pages;
use App\Models\AdminTask;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AdminTaskResource extends Resource
{
    protected static ?string $model = AdminTask::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?string $navigationLabel = 'Admin Tasks';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Task Details')->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\Select::make('category')
                    ->options(AdminTask::categories())
                    ->default('general')
                    ->required(),

                Forms\Components\Select::make('priority')
                    ->options(AdminTask::priorities())
                    ->default('normal')
                    ->required(),

                Forms\Components\Select::make('status')
                    ->options(AdminTask::statuses())
                    ->default('pending')
                    ->required(),

                Forms\Components\Select::make('assigned_to')
                    ->relationship('assignedTo', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Assign To'),

                Forms\Components\DatePicker::make('start_date')
                    ->label('Start Date'),

                Forms\Components\DatePicker::make('due_date')
                    ->label('Due Date')
                    ->after('start_date'),

                Forms\Components\Textarea::make('description')
                    ->rows(4)
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->description(fn (AdminTask $record) => $record->category
                        ? ucfirst($record->category)
                        : null
                    ),

                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Assigned To')
                    ->placeholder('Unassigned')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending'     => 'gray',
                        'in_progress' => 'warning',
                        'completed'   => 'success',
                        'cancelled'   => 'danger',
                        default       => 'gray',
                    }),

                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'low'    => 'gray',
                        'normal' => 'info',
                        'high'   => 'warning',
                        'urgent' => 'danger',
                        default  => 'gray',
                    }),

                Tables\Columns\IconColumn::make('overdue')
                    ->label('Overdue')
                    ->state(fn (AdminTask $record) => $record->isOverdue())
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-circle')
                    ->trueColor('danger')
                    ->falseIcon('heroicon-o-check-circle')
                    ->falseColor('success'),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date()
                    ->sortable()
                    ->color(fn (AdminTask $record) => $record->isOverdue() ? 'danger' : null),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Created By')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('due_date')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(AdminTask::statuses()),

                Tables\Filters\SelectFilter::make('priority')
                    ->options(AdminTask::priorities()),

                Tables\Filters\SelectFilter::make('category')
                    ->options(AdminTask::categories()),

                Tables\Filters\SelectFilter::make('assigned_to')
                    ->relationship('assignedTo', 'name')
                    ->label('Assigned To')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('overdue')
                    ->label('Overdue Only')
                    ->query(fn ($query) => $query
                        ->whereNotNull('due_date')
                        ->where('due_date', '<', now())
                        ->whereNotIn('status', ['completed', 'cancelled'])
                    ),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('mark_complete')
                    ->label('Complete')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (AdminTask $record) => ! in_array($record->status, ['completed', 'cancelled']))
                    ->requiresConfirmation()
                    ->action(function (AdminTask $record) {
                        $record->update(['status' => 'completed']);
                        \Filament\Notifications\Notification::make()
                            ->title('Task marked as completed.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reassign')
                    ->label('Reassign')
                    ->icon('heroicon-o-user-plus')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('assigned_to')
                            ->label('Assign to')
                            ->relationship('assignedTo', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->action(function (AdminTask $record, array $data) {
                        $record->update([
                            'assigned_to' => $data['assigned_to'],
                            'status'      => $record->status === 'pending' ? 'in_progress' : $record->status,
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Task reassigned.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_complete')
                        ->label('Mark as Completed')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => 'completed'])),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Task Details')->schema([
                Infolists\Components\TextEntry::make('title')->columnSpanFull(),
                Infolists\Components\TextEntry::make('category')->badge(),
                Infolists\Components\TextEntry::make('priority')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'low'    => 'gray',
                        'normal' => 'info',
                        'high'   => 'warning',
                        'urgent' => 'danger',
                        default  => 'gray',
                    }),
                Infolists\Components\TextEntry::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending'     => 'gray',
                        'in_progress' => 'warning',
                        'completed'   => 'success',
                        'cancelled'   => 'danger',
                        default       => 'gray',
                    }),
                Infolists\Components\TextEntry::make('assignedTo.name')->label('Assigned To')->placeholder('Unassigned'),
                Infolists\Components\TextEntry::make('createdBy.name')->label('Created By'),
                Infolists\Components\TextEntry::make('start_date')->date(),
                Infolists\Components\TextEntry::make('due_date')->date(),
                Infolists\Components\TextEntry::make('completed_at')->dateTime(),
                Infolists\Components\TextEntry::make('description')->columnSpanFull(),
                Infolists\Components\TextEntry::make('notes')->columnSpanFull(),
            ])->columns(3),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAdminTasks::route('/'),
            'create' => Pages\CreateAdminTask::route('/create'),
            'view'   => Pages\ViewAdminTask::route('/{record}'),
            'edit'   => Pages\EditAdminTask::route('/{record}/edit'),
        ];
    }
}
