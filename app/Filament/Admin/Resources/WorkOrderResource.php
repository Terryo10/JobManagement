<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\WorkOrderResource\Pages;
use App\Filament\Admin\Resources\WorkOrderResource\RelationManagers;
use App\Models\WorkOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkOrderResource extends Resource
{
    protected static ?string $model = WorkOrder::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?string $navigationLabel = 'Job Cards';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Job Card')->tabs([
                Forms\Components\Tabs\Tab::make('Details')->icon('heroicon-o-information-circle')->schema([
                    Forms\Components\TextInput::make('reference_number')->required()->maxLength(50)->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                    Forms\Components\Select::make('client_id')->relationship('client', 'company_name')->searchable()->preload()->required()
                        ->reactive()
                        ->afterStateUpdated(fn (Forms\Set $set) => $set('lead_id', null)),
                    Forms\Components\Select::make('lead_id')
                        ->relationship('lead', 'contact_name', fn ($query, Forms\Get $get) =>
                            $get('client_id') ? $query->where('client_id', $get('client_id')) : $query->whereRaw('1 = 0')
                        )
                        ->searchable()->preload()
                        ->disabled(fn (Forms\Get $get) => ! $get('client_id'))
                        ->helperText('Select a client first'),
                    Forms\Components\Select::make('category')
                        ->options(['media' => 'Media', 'civil_works' => 'Civil Works', 'energy' => 'Energy', 'warehouse' => 'Warehouse'])->required(),
                    Forms\Components\Select::make('status')
                        ->options(['pending' => 'Pending', 'in_progress' => 'In Progress', 'on_hold' => 'On Hold', 'completed' => 'Completed', 'cancelled' => 'Cancelled'])
                        ->default('pending')->required(),
                    Forms\Components\Select::make('priority')
                        ->options(['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent'])
                        ->default('normal')->required(),
                    Forms\Components\Select::make('assigned_department_id')
                        ->relationship('assignedDepartment', 'name')->searchable()->preload()->label('Department'),
                    Forms\Components\Textarea::make('description')->rows(4)->columnSpanFull(),
                ])->columns(2),
                Forms\Components\Tabs\Tab::make('Budget & Timeline')->icon('heroicon-o-currency-dollar')->schema([
                    Forms\Components\TextInput::make('budget')->numeric()->prefix('$'),
                    Forms\Components\TextInput::make('budget_alert_threshold')->numeric()->suffix('%')->default(80),
                    Forms\Components\DatePicker::make('start_date'),
                    Forms\Components\DatePicker::make('deadline'),
                ])->columns(2),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('reference_number')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('title')->searchable()->limit(40),
            Tables\Columns\TextColumn::make('client.company_name')->sortable(),
            Tables\Columns\TextColumn::make('claimedBy.name')->label('Claimed By')
                ->placeholder('Unclaimed')
                ->badge()
                ->color(fn ($state) => $state ? 'success' : 'gray'),
            Tables\Columns\TextColumn::make('category')->badge(),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                'pending' => 'gray', 'in_progress' => 'warning', 'on_hold' => 'info',
                'completed' => 'success', 'cancelled' => 'danger', default => 'gray',
            }),
            Tables\Columns\TextColumn::make('priority')->badge()->color(fn ($state) => match ($state) {
                'low' => 'gray', 'normal' => 'info', 'high' => 'warning', 'urgent' => 'danger', default => 'gray',
            }),
            Tables\Columns\TextColumn::make('deadline')->date()->sortable(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')->options(['pending' => 'Pending', 'in_progress' => 'In Progress', 'on_hold' => 'On Hold', 'completed' => 'Completed', 'cancelled' => 'Cancelled']),
            Tables\Filters\SelectFilter::make('category')->options(['media' => 'Media', 'civil_works' => 'Civil Works', 'energy' => 'Energy', 'warehouse' => 'Warehouse']),
            Tables\Filters\SelectFilter::make('priority')->options(['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent']),
            Tables\Filters\SelectFilter::make('claimed')
                ->options(['claimed' => 'Claimed', 'unclaimed' => 'Unclaimed'])
                ->query(function ($query, array $data) {
                    return match ($data['value'] ?? null) {
                        'claimed' => $query->whereNotNull('claimed_by'),
                        'unclaimed' => $query->whereNull('claimed_by'),
                        default => $query,
                    };
                }),
            Tables\Filters\TrashedFilter::make(),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('reassign')
                ->label('Reassign')
                ->icon('heroicon-o-user-plus')
                ->color('warning')
                ->form([
                    Forms\Components\Select::make('user_id')
                        ->label('Assign to')
                        ->options(\App\Models\User::pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                ])
                ->action(function ($record, array $data) {
                    $record->update([
                        'claimed_by' => $data['user_id'],
                        'claimed_at' => now(),
                        'status' => 'in_progress',
                    ]);
                    \Filament\Notifications\Notification::make()->title('Job reassigned.')->success()->send();
                }),
            Tables\Actions\Action::make('unassign')
                ->label('Unassign')
                ->icon('heroicon-o-user-minus')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn ($record) => $record->claimed_by !== null)
                ->action(function ($record) {
                    $record->release();
                    \Filament\Notifications\Notification::make()->title('Job unassigned and returned to queue.')->success()->send();
                }),
        ])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Job Card Details')->schema([
                Infolists\Components\TextEntry::make('reference_number'),
                Infolists\Components\TextEntry::make('title')->columnSpanFull(),
                Infolists\Components\TextEntry::make('client.company_name'),
                Infolists\Components\TextEntry::make('assignedDepartment.name')->label('Department'),
                Infolists\Components\TextEntry::make('category')->badge(),
                Infolists\Components\TextEntry::make('status')->badge()->color(fn ($state) => match ($state) {
                    'pending' => 'gray', 'in_progress' => 'warning', 'on_hold' => 'info',
                    'completed' => 'success', 'cancelled' => 'danger', default => 'gray',
                }),
                Infolists\Components\TextEntry::make('priority')->badge()->color(fn ($state) => match ($state) {
                    'low' => 'gray', 'normal' => 'info', 'high' => 'warning', 'urgent' => 'danger', default => 'gray',
                }),
                Infolists\Components\TextEntry::make('description')->columnSpanFull(),
            ])->columns(3),
            Infolists\Components\Section::make('Budget & Timeline')->schema([
                Infolists\Components\TextEntry::make('budget')->money('usd'),
                Infolists\Components\TextEntry::make('actual_cost')->money('usd'),
                Infolists\Components\TextEntry::make('start_date')->date(),
                Infolists\Components\TextEntry::make('deadline')->date(),
                Infolists\Components\TextEntry::make('completed_at')->dateTime(),
            ])->columns(3),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TasksRelationManager::class,
            RelationManagers\NotesRelationManager::class,
            RelationManagers\MaterialsRelationManager::class,
            RelationManagers\DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListWorkOrders::route('/'),
            'create' => Pages\CreateWorkOrder::route('/create'),
            'view'   => Pages\ViewWorkOrder::route('/{record}'),
            'edit'   => Pages\EditWorkOrder::route('/{record}/edit'),
        ];
    }
}
