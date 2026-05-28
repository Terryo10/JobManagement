<?php

namespace App\Filament\Admin\Resources\BillboardResource\Pages;

use App\Filament\Admin\Resources\BillboardResource;
use App\Filament\Admin\Resources\BillboardResource\Widgets\BillboardStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBillboards extends ListRecords
{
    protected static string $resource = BillboardResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BillboardStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'static' => Tab::make('Static Billboards')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'static')),
            'buses' => Tab::make('Buses')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'bus')),
            'kombies' => Tab::make('Kombies')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'kombi')),
        ];
    }
}
