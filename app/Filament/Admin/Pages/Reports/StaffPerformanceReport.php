<?php

namespace App\Filament\Admin\Pages\Reports;

use App\Models\Task;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StaffPerformanceReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?string $navigationLabel = 'Staff Performance';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.admin.pages.reports.staff-performance-report';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Task::query()
                    ->select('assigned_to')
                    ->selectRaw('MAX(id) as id')
                    ->selectRaw('COUNT(*) as total_tasks')
                    ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tasks")
                    ->selectRaw('COALESCE(SUM(actual_hours), 0) as total_actual_hours')
                    ->selectRaw('COALESCE(SUM(estimated_hours), 0) as total_estimated_hours')
                    ->whereNotNull('assigned_to')
                    ->groupBy('assigned_to')
            )
            ->columns([
                TextColumn::make('assignedTo.name')->label('Staff Member')->searchable(),
                TextColumn::make('total_tasks')->label('Total Tasks')->sortable(),
                TextColumn::make('completed_tasks')->label('Completed')->sortable(),
                TextColumn::make('completion_rate')
                    ->label('Completion %')
                    ->getStateUsing(fn ($record) => $record->total_tasks > 0
                        ? round(($record->completed_tasks / $record->total_tasks) * 100) . '%'
                        : '0%')
                    ->badge()
                    ->color(fn ($state) => (int) $state >= 80 ? 'success' : ((int) $state >= 50 ? 'warning' : 'danger')),
                TextColumn::make('total_actual_hours')->label('Actual Hrs')->numeric(1)->suffix(' hrs'),
                TextColumn::make('total_estimated_hours')->label('Est. Hrs')->numeric(1)->suffix(' hrs'),
                TextColumn::make('efficiency')
                    ->label('Efficiency')
                    ->getStateUsing(fn ($record) => $record->total_estimated_hours > 0
                        ? round(($record->total_actual_hours / $record->total_estimated_hours) * 100) . '%'
                        : '—'),
            ])
            ->defaultSort('total_tasks', 'desc');
    }
}
