<?php

namespace App\Filament\Marketing\Widgets;

use App\Models\Proposal;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentProposalsWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected static ?string $heading = 'Recent Proposals';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Proposal::query()
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')->limit(30),
                Tables\Columns\TextColumn::make('client.company_name')->label('Client')->limit(20),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'draft' => 'gray', 'submitted' => 'info', 'accepted' => 'success', 'rejected' => 'danger', default => 'gray',
                }),
                Tables\Columns\TextColumn::make('value')->money(fn ($record) => $record->currency),
            ])
            ->paginated(false)
            ->recordUrl(fn (Proposal $record): string => route('filament.marketing.resources.proposals.edit', ['record' => $record]));
    }
}
