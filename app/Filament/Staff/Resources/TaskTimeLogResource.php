<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\TaskTimeLogResource\Pages;
use App\Models\TaskTimeLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TaskTimeLogResource extends Resource
{
    protected static ?string $model = TaskTimeLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'My Time Logs';
    protected static ?string $navigationGroup = 'My Work';
    protected static ?int $navigationSort = 3;
    protected static bool $shouldRegisterNavigation = false;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('task_id')->relationship('task', 'title')->searchable()->preload()->required(),
            Forms\Components\Hidden::make('user_id')->default(fn () => auth()->id()),
            Forms\Components\DateTimePicker::make('started_at')->required(),
            Forms\Components\DateTimePicker::make('ended_at'),
            Forms\Components\TextInput::make('duration_minutes')->numeric()->label('Duration (minutes)'),
            Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('task.title')->label('Task')->limit(40),
            Tables\Columns\TextColumn::make('started_at')->dateTime()->sortable(),
            Tables\Columns\TextColumn::make('ended_at')->dateTime(),
            Tables\Columns\TextColumn::make('duration_minutes')->label('Duration (min)'),
        ])
        ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTaskTimeLogs::route('/'),
            'create' => Pages\CreateTaskTimeLog::route('/create'),
            'edit'   => Pages\EditTaskTimeLog::route('/{record}/edit'),
        ];
    }
}
