<?php

namespace App\Filament\Staff\Resources\PersonalFileResource\Pages;

use App\Filament\Staff\Resources\PersonalFileResource;
use App\Models\PersonalFile;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPersonalFiles extends ListRecords
{
    protected static string $resource = PersonalFileResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }

    protected function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('sharedWith');
    }

    public function getTabs(): array
    {
        return [
            'mine' => Tab::make('My Files')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('user_id', auth()->id())->orWhereHas('sharedWith', fn ($query) => $query->where('user_id', auth()->id())))
                ->badge(fn () => PersonalFile::where('user_id', auth()->id())->orWhereHas('sharedWith', fn ($query) => $query->where('user_id', auth()->id()))->count()),

            'shared' => Tab::make('Shared with me')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('sharedWith', fn ($query) => $query->where('user_id', auth()->id())))
                ->badge(fn () => PersonalFile::whereHas('sharedWith', fn ($query) => $query->where('user_id', auth()->id()))->count()),
        ];
    }
}
