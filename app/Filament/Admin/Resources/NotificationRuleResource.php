<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\NotificationRuleResource\Pages;
use App\Models\NotificationRule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationRuleResource extends Resource
{
    protected static ?string $model = NotificationRule::class;
    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $navigationGroup = 'HR';
    protected static ?string $navigationLabel = 'Notification Rules';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Rule Configuration')->schema([
                Forms\Components\TextInput::make('label')->required()->maxLength(255)
                    ->helperText('Human-readable name for this rule'),
                Forms\Components\TextInput::make('rule_key')->required()->maxLength(100)
                    ->unique(ignoreRecord: true)->helperText('Unique identifier (e.g., deadline_reminder_3d)'),
                Forms\Components\Select::make('rule_type')
                    ->options([
                        'deadline_reminder' => '⏰ Deadline Reminder',
                        'task_overdue' => '🔴 Task Overdue Nudge',
                        'budget_threshold' => '💰 Budget Threshold Alert',
                        'stock_low' => '📦 Low Stock Alert',
                        'invoice_overdue' => '💳 Invoice Overdue',
                        'status_change' => '🔄 Status Change',
                    ])
                    ->required()->native(false),
                Forms\Components\TextInput::make('value')->required()->maxLength(255)
                    ->helperText('Threshold value (e.g., hours, percentage, quantity)'),
                Forms\Components\TextInput::make('trigger_days')->numeric()
                    ->helperText('Days before/after event to trigger (e.g., 3 = 3 days before deadline)')
                    ->suffix('days'),
                Forms\Components\Select::make('applies_to_role')
                    ->options([
                        'super_admin' => 'Super Admin',
                        'manager' => 'Manager',
                        'dept_head' => 'Dept Head',
                        'staff' => 'Staff',
                        'client' => 'Client',
                    ])
                    ->placeholder('All roles')
                    ->helperText('Leave blank to notify all roles'),
                Forms\Components\Toggle::make('is_active')->default(true)->label('Active'),
                Forms\Components\Textarea::make('description')->rows(2)->columnSpanFull()
                    ->helperText('Describe when and why this rule should fire'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('label')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('rule_type')->badge()
                ->color(fn ($state) => match ($state) {
                    'deadline_reminder' => 'warning', 'task_overdue' => 'danger',
                    'budget_threshold' => 'info', 'stock_low' => 'warning',
                    'invoice_overdue' => 'danger', 'status_change' => 'primary',
                    default => 'gray',
                }),
            Tables\Columns\TextColumn::make('value')->label('Threshold'),
            Tables\Columns\TextColumn::make('trigger_days')->suffix(' days')->label('Trigger'),
            Tables\Columns\TextColumn::make('applies_to_role')->label('Role')->placeholder('All'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('rule_type')->options([
                'deadline_reminder' => 'Deadline Reminder', 'task_overdue' => 'Task Overdue',
                'budget_threshold' => 'Budget Threshold', 'stock_low' => 'Low Stock',
                'invoice_overdue' => 'Invoice Overdue', 'status_change' => 'Status Change',
            ]),
            Tables\Filters\TernaryFilter::make('is_active'),
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
            'index'  => Pages\ListNotificationRules::route('/'),
            'create' => Pages\CreateNotificationRule::route('/create'),
            'edit'   => Pages\EditNotificationRule::route('/{record}/edit'),
        ];
    }
}
