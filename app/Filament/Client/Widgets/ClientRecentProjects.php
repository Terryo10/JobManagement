<?php

namespace App\Filament\Client\Widgets;

use App\Models\WorkOrder;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ClientRecentProjects extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'My Projects';

    public function table(Table $table): Table
    {
        $email = auth()->user()?->email;

        return $table
            ->query(
                WorkOrder::whereHas('client', fn ($q) => $q->where('email', $email))
                    ->orderBy('updated_at', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')->label('Ref #')->sortable(),
                Tables\Columns\TextColumn::make('title')->limit(40),
                Tables\Columns\TextColumn::make('category')->badge()
                    ->color(fn ($state) => match ($state) {
                        'media' => 'primary', 'civil_works' => 'warning',
                        'energy' => 'success', 'warehouse' => 'info', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn ($state) => match ($state) {
                        'pending' => 'gray', 'in_progress' => 'warning', 'on_hold' => 'info',
                        'completed' => 'success', 'cancelled' => 'danger', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('deadline')->date()->sortable()
                    ->color(fn ($record) => $record->deadline?->isPast() && $record->status !== 'completed' ? 'danger' : null),
                Tables\Columns\TextColumn::make('updated_at')->since()->label('Last Update'),
            ])
            ->paginated([5])
            ->emptyStateHeading('No projects yet')
            ->emptyStateDescription('Your projects will appear here once created')
            ->emptyStateIcon('heroicon-o-clipboard-document-list');
    }
}
