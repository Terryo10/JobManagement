<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\TaskResource\Pages;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;
    protected static ?string $navigationIcon = 'heroicon-o-check-circle';
    protected static ?string $navigationLabel = 'My Tasks';
    protected static ?string $navigationGroup = 'My Work';
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('assigned_to', auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->disabled(),
            Forms\Components\Select::make('status')->options(['pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'blocked' => 'Blocked'])->required(),
            Forms\Components\TextInput::make('completion_percentage')->numeric()->suffix('%'),
            Forms\Components\Textarea::make('description')->rows(4)->disabled()->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->searchable()->limit(50),
            Tables\Columns\TextColumn::make('workOrder.reference_number')->label('Work Order'),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) { 'pending' => 'gray', 'in_progress' => 'warning', 'completed' => 'success', 'blocked' => 'danger', default => 'gray' }),
            Tables\Columns\TextColumn::make('priority')->badge(),
            Tables\Columns\TextColumn::make('completion_percentage')->suffix('%'),
            Tables\Columns\TextColumn::make('deadline')->date()->sortable(),
        ])
        ->filters([Tables\Filters\SelectFilter::make('status')->options(['pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'blocked' => 'Blocked'])])
        ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit'   => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
