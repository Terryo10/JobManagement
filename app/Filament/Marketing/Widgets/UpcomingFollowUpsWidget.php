<?php

namespace App\Filament\Marketing\Widgets;

use App\Models\Lead;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class UpcomingFollowUpsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Upcoming Follow-ups (Next 7 Days)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Lead::query()
                    ->whereIn('status', ['new', 'in_progress'])
                    ->whereNotNull('follow_up_date')
                    ->whereBetween('follow_up_date', [now()->startOfDay(), now()->addDays(7)->endOfDay()])
                    ->orderBy('follow_up_date', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('contact_name')->searchable(),
                Tables\Columns\TextColumn::make('company_name'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'new' => 'info', 'in_progress' => 'warning', default => 'gray',
                }),
                Tables\Columns\TextColumn::make('follow_up_date')->date()
                    ->color(fn ($record) => $record->follow_up_date && $record->follow_up_date->isToday() ? 'warning' : 'primary'),
                Tables\Columns\TextColumn::make('assignedTo.name')->label('Assigned To'),
            ])
            ->paginated(false)
            ->recordUrl(fn (Lead $record): string => route('filament.marketing.resources.leads.edit', ['record' => $record]));
    }
}
