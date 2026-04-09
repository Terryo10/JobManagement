<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DeletionRequestResource\Pages;
use App\Models\DeletionRequest;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DeletionRequestResource extends Resource
{
    protected static ?string $model = DeletionRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';
    protected static ?string $navigationGroup = 'System';
    protected static ?string $navigationLabel = 'Deletion Requests';
    protected static ?string $pluralModelLabel = 'Deletion Requests';
    protected static ?string $modelLabel = 'Deletion Request';
    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = DeletionRequest::pending()->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('requestedBy.name')
                    ->label('Requested By')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('short_type')
                    ->label('Record Type')
                    ->state(fn (DeletionRequest $record) => class_basename($record->subject_type))
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('subject_label')
                    ->label('Record')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('reason')
                    ->limit(40)
                    ->placeholder('No reason given')
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending'  => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => 'gray',
                    }),

                Tables\Columns\TextColumn::make('reviewedBy.name')
                    ->label('Reviewed By')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('reviewed_at')
                    ->label('Reviewed')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'  => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve deletion')
                    ->modalDescription(fn (DeletionRequest $record) =>
                        "This will permanently delete \"{$record->subject_label}\". This action cannot be undone.")
                    ->visible(fn (DeletionRequest $record) => $record->status === 'pending')
                    ->action(function (DeletionRequest $record) {
                        $record->approve(auth()->id());

                        Notification::make()
                            ->title('Deletion approved')
                            ->body("'{$record->subject_label}' has been deleted.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reject deletion request')
                    ->modalDescription('The record will not be deleted and the requester will be notified.')
                    ->visible(fn (DeletionRequest $record) => $record->status === 'pending')
                    ->action(function (DeletionRequest $record) {
                        $record->reject(auth()->id());

                        Notification::make()
                            ->title('Deletion request rejected')
                            ->info()
                            ->send();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeletionRequests::route('/'),
        ];
    }
}
