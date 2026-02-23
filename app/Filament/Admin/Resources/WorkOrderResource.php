<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\WorkOrderResource\Pages;
use App\Models\WorkOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkOrderResource extends Resource
{
    protected static ?string $model = WorkOrder::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Work Order Details')->schema([
                Forms\Components\TextInput::make('reference_number')->required()->maxLength(50)->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                Forms\Components\Select::make('client_id')->relationship('client', 'company_name')->searchable()->preload()->required(),
                Forms\Components\Select::make('lead_id')->relationship('lead', 'contact_name')->searchable()->preload(),
                Forms\Components\Select::make('category')->options(['media' => 'Media', 'civil_works' => 'Civil Works', 'energy' => 'Energy', 'warehouse' => 'Warehouse'])->required(),
                Forms\Components\Select::make('status')->options(['pending' => 'Pending', 'in_progress' => 'In Progress', 'on_hold' => 'On Hold', 'completed' => 'Completed', 'cancelled' => 'Cancelled'])->default('pending')->required(),
                Forms\Components\Select::make('priority')->options(['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent'])->default('normal')->required(),
                Forms\Components\Select::make('assigned_department_id')->relationship('assignedDepartment', 'name')->searchable()->preload()->label('Assigned Department'),
                Forms\Components\TextInput::make('budget')->numeric()->prefix('USD'),
                Forms\Components\TextInput::make('budget_alert_threshold')->numeric()->suffix('%')->default(80),
                Forms\Components\DatePicker::make('start_date'),
                Forms\Components\DatePicker::make('deadline'),
                Forms\Components\Textarea::make('description')->rows(4)->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('reference_number')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('title')->searchable()->limit(40),
            Tables\Columns\TextColumn::make('client.company_name')->sortable(),
            Tables\Columns\TextColumn::make('category')->badge(),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) { 'pending' => 'gray', 'in_progress' => 'warning', 'on_hold' => 'info', 'completed' => 'success', 'cancelled' => 'danger', default => 'gray' }),
            Tables\Columns\TextColumn::make('priority')->badge()->color(fn ($state) => match ($state) { 'low' => 'gray', 'normal' => 'info', 'high' => 'warning', 'urgent' => 'danger', default => 'gray' }),
            Tables\Columns\TextColumn::make('deadline')->date()->sortable(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')->options(['pending' => 'Pending', 'in_progress' => 'In Progress', 'on_hold' => 'On Hold', 'completed' => 'Completed', 'cancelled' => 'Cancelled']),
            Tables\Filters\SelectFilter::make('category')->options(['media' => 'Media', 'civil_works' => 'Civil Works', 'energy' => 'Energy', 'warehouse' => 'Warehouse']),
            Tables\Filters\SelectFilter::make('priority')->options(['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent']),
            Tables\Filters\TrashedFilter::make(),
        ])
        ->actions([Tables\Actions\EditAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListWorkOrders::route('/'),
            'create' => Pages\CreateWorkOrder::route('/create'),
            'edit'   => Pages\EditWorkOrder::route('/{record}/edit'),
        ];
    }
}
