<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Lead;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentLeadsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 1;
    protected static ?string $heading = 'Recent Leads';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Lead::query()
                    ->whereIn('status', ['new', 'in_progress'])
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('contact_name')->searchable(),
                Tables\Columns\TextColumn::make('company_name')->limit(20),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'new' => 'info', 'in_progress' => 'warning', default => 'gray',
                }),
                Tables\Columns\TextColumn::make('follow_up_date')->date()
                    ->color(fn ($record) => $record->follow_up_date && $record->follow_up_date->isPast() ? 'danger' : null),
            ])
            ->paginated(false);
    }
}
