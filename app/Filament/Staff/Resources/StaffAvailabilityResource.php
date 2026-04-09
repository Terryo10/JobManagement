<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\StaffAvailabilityResource\Pages;
use App\Models\StaffAvailability;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Shared\Concerns\EnforcesAdminDelete;

class StaffAvailabilityResource extends Resource
{
    use EnforcesAdminDelete;
    protected static ?string $model = StaffAvailability::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Leave Requests';
    protected static ?string $navigationGroup = 'My Work';
    protected static ?int $navigationSort = 4;
    protected static ?string $modelLabel = 'Leave Request';
    protected static ?string $pluralModelLabel = 'Leave Requests';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Hidden::make('user_id')->default(fn () => auth()->id()),
            Forms\Components\Section::make('Leave Request Details')
                ->description('Management will be notified and your calendar will be blocked for this period.')
                ->schema([
                    Forms\Components\DatePicker::make('unavailable_from')
                        ->label('From')
                        ->required()
                        ->native(false),
                    Forms\Components\DatePicker::make('unavailable_to')
                        ->label('Until (inclusive)')
                        ->required()
                        ->native(false)
                        ->afterOrEqual('unavailable_from'),
                    Forms\Components\Select::make('reason')
                        ->label('Reason')
                        ->options([
                            'leave'            => 'Annual Leave',
                            'sick'             => 'Sick Leave',
                            'field_deployment' => 'Field Deployment',
                            'training'         => 'Training',
                            'other'            => 'Other',
                        ])
                        ->required(),
                    Forms\Components\Textarea::make('notes')
                        ->label('Additional Notes')
                        ->rows(2)
                        ->placeholder('Any extra context for your manager...')
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('unavailable_from')
                    ->label('From')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('unavailable_to')
                    ->label('Until')
                    ->date('d M Y')
                    ->sortable(),
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
                    ->placeholder('—')
                    ->limit(50)
                    ->wrap(),
            ])
            ->defaultSort('unavailable_from', 'desc')
            ->actions([
                // Staff can only edit/delete pending requests
                Tables\Actions\EditAction::make()
                    ->hidden(fn (StaffAvailability $record) => $record->status !== 'pending'),
                \App\Filament\Shared\Actions\RequestDeletionTableAction::make()
                    ->hidden(fn (StaffAvailability $record) => $record->status !== 'pending'),
            ])
            ->emptyStateHeading('No leave requests yet')
            ->emptyStateDescription('Submit a request so management knows when you\'re unavailable. Your calendar will show blocked-out periods.')
            ->emptyStateIcon('heroicon-o-calendar-days');
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
