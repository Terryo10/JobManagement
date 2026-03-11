<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\AllWorkOrdersResource\Pages;
use App\Filament\Staff\Resources\WorkOrderResource\RelationManagers\CollaboratorsRelationManager;
use App\Filament\Staff\Resources\WorkOrderResource\RelationManagers\TasksRelationManager;
use App\Models\WorkOrder;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AllWorkOrdersResource extends Resource
{
    protected static ?string $model = WorkOrder::class;
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationLabel = 'All Work Orders';
    protected static ?string $navigationGroup = 'Work Orders';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'all-work-orders';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('title')
                    ->limit(40)
                    ->searchable(),
                Tables\Columns\TextColumn::make('client.company_name')
                    ->label('Client')
                    ->searchable(),
                Tables\Columns\TextColumn::make('claimedBy.name')
                    ->label('Assigned To')
                    ->placeholder('— Unassigned —')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'media' => 'primary',
                        'civil_works' => 'warning',
                        'energy' => 'success',
                        'warehouse' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'pending' => 'gray',
                        'in_progress' => 'warning',
                        'on_hold' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'low' => 'gray',
                        'normal' => 'info',
                        'high' => 'warning',
                        'urgent' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('deadline')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => $record->deadline && $record->deadline->isPast() ? 'danger' : null),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('assignment')
                    ->label('Assignment')
                    ->options([
                        'unassigned' => 'Unassigned',
                        'assigned' => 'Assigned',
                        'mine' => 'Assigned to Me',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'unassigned' => $query->whereNull('claimed_by'),
                            'assigned' => $query->whereNotNull('claimed_by'),
                            'mine' => $query->where('claimed_by', auth()->id()),
                            default => $query,
                        };
                    }),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'on_hold' => 'On Hold',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'normal' => 'Normal',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ]),
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'media' => 'Media',
                        'civil_works' => 'Civil Works',
                        'energy' => 'Energy',
                        'warehouse' => 'Warehouse',
                    ]),
            ])
            ->filtersLayout(Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\Action::make('claim')
                    ->label('Claim')
                    ->icon('heroicon-o-hand-raised')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Claim this work order?')
                    ->modalDescription('You will be assigned to this work order and it will move to "In Progress".')
                    ->visible(fn ($record) => $record->claimed_by === null)
                    ->action(function ($record) {
                        $success = $record->claim(auth()->user());
                        if ($success) {
                            \Filament\Notifications\Notification::make()->title('Work order claimed!')->success()->send();
                        } else {
                            \Filament\Notifications\Notification::make()->title('Already claimed by someone else.')->danger()->send();
                        }
                    }),
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('deadline')
            ->poll('30s');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Job Details')->schema([
                Infolists\Components\TextEntry::make('reference_number'),
                Infolists\Components\TextEntry::make('title')->columnSpanFull(),
                Infolists\Components\TextEntry::make('client.company_name')->label('Client'),
                Infolists\Components\TextEntry::make('category')->badge(),
                Infolists\Components\TextEntry::make('status')->badge(),
                Infolists\Components\TextEntry::make('priority')->badge(),
                Infolists\Components\TextEntry::make('claimedBy.name')->label('Assigned To')->placeholder('— Unassigned —'),
                Infolists\Components\TextEntry::make('start_date')->date(),
                Infolists\Components\TextEntry::make('deadline')->date(),
                Infolists\Components\TextEntry::make('description')->html()->columnSpanFull(),
            ])->columns(3),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            TasksRelationManager::class,
            CollaboratorsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAllWorkOrders::route('/'),
            'view'  => Pages\ViewAllWorkOrder::route('/{record}'),
        ];
    }
}
