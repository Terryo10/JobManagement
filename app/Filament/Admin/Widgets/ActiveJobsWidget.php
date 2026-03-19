<?php

namespace App\Filament\Admin\Widgets;

use App\Models\WorkOrder;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ActiveJobsWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Active Jobs';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                WorkOrder::query()
                    ->whereIn('status', ['pending', 'in_progress', 'on_hold'])
                    ->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")
                    ->orderBy('deadline')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')->label('Ref #')->sortable(),
                Tables\Columns\TextColumn::make('title')->limit(35)->searchable(),
                Tables\Columns\TextColumn::make('client.company_name')->label('Client')->limit(20),
                Tables\Columns\TextColumn::make('claimedBy.name')->label('Claimed By')
                    ->placeholder('—')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('category')->badge()
                    ->color(fn ($state) => match ($state) {
                        'media' => 'primary', 'civil_works' => 'warning',
                        'energy' => 'success', 'warehouse' => 'info', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'pending' => 'gray', 'in_progress' => 'warning', 'on_hold' => 'info', default => 'gray',
                }),
                Tables\Columns\TextColumn::make('priority')->badge()->color(fn ($state) => match ($state) {
                    'urgent' => 'danger', 'high' => 'warning', 'normal' => 'info', 'low' => 'gray', default => 'gray',
                }),
                Tables\Columns\TextColumn::make('deadline')->date()->sortable()
                    ->color(fn ($record) => $record->deadline && $record->deadline->isPast() ? 'danger' : null),
            ])
            ->paginated(false)
            ->recordUrl(fn ($record) => route('filament.admin.resources.work-orders.view', $record));
    }
}
