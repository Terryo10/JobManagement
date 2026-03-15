<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\StaffAvailabilityResource\Pages;
use App\Models\StaffAvailability;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StaffAvailabilityResource extends Resource
{
    protected static ?string $model = StaffAvailability::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'HR';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')->relationship('user', 'name')->searchable()->preload()->required(),
            Forms\Components\DatePicker::make('unavailable_from')->required(),
            Forms\Components\DatePicker::make('unavailable_to')->required(),
            Forms\Components\Select::make('reason')->options([
                'leave'            => 'Annual Leave',
                'sick'             => 'Sick Leave',
                'field_deployment' => 'Field Deployment',
                'training'         => 'Training',
                'other'            => 'Other',
            ]),
            Forms\Components\Textarea::make('notes')->rows(3),
            Forms\Components\Select::make('approved_by')->relationship('approvedBy', 'name')->searchable()->preload()->label('Approved By'),
            Forms\Components\Select::make('status')->options([
                'pending'  => 'Pending',
                'approved' => 'Approved',
                'denied'   => 'Denied',
            ])->default('pending'),
            Forms\Components\Textarea::make('admin_note')->label('Admin Note (optional)')->rows(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Staff Member')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('unavailable_from')->label('From')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('unavailable_to')->label('Until')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('reason')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'leave'            => 'Annual Leave',
                        'sick'             => 'Sick Leave',
                        'field_deployment' => 'Field Deployment',
                        'training'         => 'Training',
                        default            => ucfirst($state ?? 'Other'),
                    })
                    ->color(fn ($state) => match ($state) {
                        'leave'            => 'info',
                        'sick'             => 'warning',
                        'field_deployment' => 'success',
                        'training'         => 'primary',
                        default            => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'approved' => 'success',
                        'denied'   => 'danger',
                        default    => 'warning',
                    }),
                Tables\Columns\TextColumn::make('admin_note')
                    ->label('Admin Note')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('approvedBy.name')->label('Actioned By')->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending'  => 'Pending',
                    'approved' => 'Approved',
                    'denied'   => 'Denied',
                ]),
                Tables\Filters\SelectFilter::make('reason')->options([
                    'leave'            => 'Annual Leave',
                    'sick'             => 'Sick Leave',
                    'field_deployment' => 'Field Deployment',
                    'training'         => 'Training',
                    'other'            => 'Other',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->hidden(fn (StaffAvailability $record) => $record->status === 'approved')
                    ->form([
                        Forms\Components\Textarea::make('admin_note')
                            ->label('Note to staff (optional)')
                            ->placeholder('e.g. Enjoy your leave! / Please make sure handover is done.')
                            ->rows(3),
                    ])
                    ->action(function (StaffAvailability $record, array $data) {
                        $record->update([
                            'status'      => 'approved',
                            'approved_by' => auth()->id(),
                            'admin_note'  => $data['admin_note'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Request approved')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('deny')
                    ->label('Deny')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->hidden(fn (StaffAvailability $record) => $record->status === 'denied')
                    ->form([
                        Forms\Components\Textarea::make('admin_note')
                            ->label('Reason for denial (optional)')
                            ->placeholder('e.g. Critical project deadline during this period.')
                            ->rows(3),
                    ])
                    ->action(function (StaffAvailability $record, array $data) {
                        $record->update([
                            'status'      => 'denied',
                            'approved_by' => auth()->id(),
                            'admin_note'  => $data['admin_note'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Request denied')
                            ->danger()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStaffAvailabilities::route('/'),
            'create' => Pages\CreateStaffAvailability::route('/create'),
            'edit'   => Pages\EditStaffAvailability::route('/{record}/edit'),
        ];
    }
}
