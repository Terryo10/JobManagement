<?php

namespace App\Filament\Client\Resources\PersonalFileResource\Pages;

use App\Filament\Client\Resources\PersonalFileResource;
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

    public function getTabs(): array
    {
        return [
            'mine' => Tab::make('My Files')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('user_id', auth()->id()))
                ->badge(fn () => PersonalFile::where('user_id', auth()->id())->count()),

            'shared' => Tab::make('Shared Files')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_shared', true))
                ->badge(fn () => PersonalFile::where('is_shared', true)->count()),
        ];
    }
}
