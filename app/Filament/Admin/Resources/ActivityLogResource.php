<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';
    protected static ?string $navigationGroup = 'System';
    protected static ?string $navigationLabel = 'Activity Logs';
    protected static ?string $pluralModelLabel = 'Activity Logs';
    protected static ?string $modelLabel = 'Activity Log';
    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->placeholder('System'),

                Tables\Columns\TextColumn::make('action')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'deleted'            => 'danger',
                        'restored'           => 'success',
                        'deletion_requested' => 'warning',
                        'deletion_approved'  => 'danger',
                        'deletion_rejected'  => 'info',
                        default              => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => str_replace('_', ' ', ucfirst($state))),

                Tables\Columns\TextColumn::make('short_type')
                    ->label('Record Type')
                    ->state(fn (ActivityLog $record) => $record->getShortSubjectType())
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('subject_label')
                    ->label('Record')
                    ->searchable()
                    ->limit(50),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options([
                        'deleted'            => 'Deleted',
                        'restored'           => 'Restored',
                        'deletion_requested' => 'Deletion Requested',
                        'deletion_approved'  => 'Deletion Approved',
                        'deletion_rejected'  => 'Deletion Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('subject_type')
                    ->label('Record Type')
                    ->options(fn () => ActivityLog::query()
                        ->select('subject_type')
                        ->distinct()
                        ->pluck('subject_type', 'subject_type')
                        ->mapWithKeys(fn ($v) => [$v => class_basename($v)])
                        ->toArray()
                    ),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }
}
