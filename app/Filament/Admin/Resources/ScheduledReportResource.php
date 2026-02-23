<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ScheduledReportResource\Pages;
use App\Models\ScheduledReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ScheduledReportResource extends Resource
{
    protected static ?string $model = ScheduledReport::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\Select::make('report_type')->options(['operational' => 'Operational', 'client' => 'Client', 'financial' => 'Financial', 'staff' => 'Staff', 'overdue' => 'Overdue'])->required(),
            Forms\Components\Select::make('frequency')->options(['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly'])->required(),
            Forms\Components\TextInput::make('day_of_week')->numeric()->label('Day of Week (0=Mon)'),
            Forms\Components\TimePicker::make('time_of_day')->default('08:00'),
            Forms\Components\Select::make('export_format')->options(['pdf' => 'PDF', 'excel' => 'Excel', 'csv' => 'CSV'])->default('pdf')->required(),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\Textarea::make('recipients')->label('Recipients (JSON array)')->rows(3)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('report_type')->badge(),
            Tables\Columns\TextColumn::make('frequency')->badge(),
            Tables\Columns\TextColumn::make('export_format')->badge(),
            Tables\Columns\TextColumn::make('last_sent_at')->dateTime()->sortable(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('report_type')->options(['operational' => 'Operational', 'client' => 'Client', 'financial' => 'Financial', 'staff' => 'Staff', 'overdue' => 'Overdue']),
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
            'index'  => Pages\ListScheduledReports::route('/'),
            'create' => Pages\CreateScheduledReport::route('/create'),
            'edit'   => Pages\EditScheduledReport::route('/{record}/edit'),
        ];
    }
}
