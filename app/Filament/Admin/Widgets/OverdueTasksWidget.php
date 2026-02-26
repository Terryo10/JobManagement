<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Task;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class OverdueTasksWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Priority Tasks';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Task::query()
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->where(function ($q) {
                        $q->where('deadline', '<', now())
                          ->orWhere('priority', 'urgent')
                          ->orWhere('priority', 'high');
                    })
                    ->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")
                    ->orderBy('deadline')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')->limit(40)->searchable(),
                Tables\Columns\TextColumn::make('workOrder.reference_number')->label('Job Card'),
                Tables\Columns\TextColumn::make('assignedTo.name')->label('Assigned'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'pending' => 'gray', 'in_progress' => 'warning', 'blocked' => 'danger', default => 'gray',
                }),
                Tables\Columns\TextColumn::make('priority')->badge()->color(fn ($state) => match ($state) {
                    'urgent' => 'danger', 'high' => 'warning', 'normal' => 'info', default => 'gray',
                }),
                Tables\Columns\TextColumn::make('deadline')->date()->sortable()
                    ->color(fn ($record) => $record->deadline && $record->deadline->isPast() ? 'danger' : null),
                Tables\Columns\TextColumn::make('completion_percentage')->suffix('%'),
            ])
            ->paginated(false);
    }
}
