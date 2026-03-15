<?php

namespace App\Filament\Admin\Resources\TaskResource\Pages;

use App\Filament\Admin\Resources\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }

    public function getTabs(): array
    {
        return [
            'active' => Tab::make('Active Tasks')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotIn('status', ['completed', 'cancelled']))
                ->badge(fn () => \App\Models\Task::whereNotIn('status', ['completed', 'cancelled'])->count()),
            'completed' => Tab::make('Completed Tasks')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed'))
                ->badge(fn () => \App\Models\Task::where('status', 'completed')->count()),
        ];
    }
}
