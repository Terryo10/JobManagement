<?php

namespace App\Filament\Marketing\Widgets;

use App\Models\NetworkingEvent;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingEventsWidget extends BaseWidget
{
    protected static ?int $sort = 5;
    protected static ?string $heading = 'Upcoming Events';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                NetworkingEvent::query()
                    ->where('start_date', '>=', now()->startOfDay())
                    ->orderBy('start_date', 'asc')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')->limit(30),
                Tables\Columns\TextColumn::make('type')->badge()->color(fn ($state) => match ($state) {
                    'conference' => 'primary', 'seminar' => 'info', 'networking' => 'success', default => 'gray',
                }),
                Tables\Columns\TextColumn::make('start_date')->date(),
                Tables\Columns\TextColumn::make('location')->limit(20),
            ])
            ->paginated(false)
            ->recordUrl(fn (NetworkingEvent $record): string => route('filament.marketing.resources.networking-events.edit', ['record' => $record]));
    }
}
