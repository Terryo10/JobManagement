<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SafetyComplianceRecordResource\Pages;
use App\Models\SafetyComplianceRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SafetyComplianceRecordResource extends Resource
{
    protected static ?string $model = SafetyComplianceRecord::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('work_order_id')->relationship('workOrder', 'reference_number')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('checklist_item')->required()->maxLength(255),
            Forms\Components\Toggle::make('is_complete')->default(false),
            Forms\Components\Select::make('completed_by')->relationship('completedBy', 'name')->searchable()->preload(),
            Forms\Components\DateTimePicker::make('completed_at'),
            Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('workOrder.reference_number')->label('Work Order')->sortable(),
            Tables\Columns\TextColumn::make('checklist_item')->searchable()->limit(50),
            Tables\Columns\IconColumn::make('is_complete')->boolean(),
            Tables\Columns\TextColumn::make('completedBy.name')->label('Completed By'),
            Tables\Columns\TextColumn::make('completed_at')->dateTime()->sortable(),
        ])
        ->filters([Tables\Filters\TernaryFilter::make('is_complete')])
        ->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Admin\Resources\SafetyComplianceRecordResource\RelationManagers\DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSafetyComplianceRecords::route('/'),
            'create' => Pages\CreateSafetyComplianceRecord::route('/create'),
            'view'   => Pages\ViewSafetyComplianceRecord::route('/{record}'),
            'edit'   => Pages\EditSafetyComplianceRecord::route('/{record}/edit'),
        ];
    }
}
