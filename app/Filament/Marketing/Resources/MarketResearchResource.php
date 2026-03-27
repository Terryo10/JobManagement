<?php

namespace App\Filament\Marketing\Resources;

use App\Filament\Marketing\Resources\MarketResearchResource\Pages;
use App\Models\MarketResearch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Shared\Concerns\EnforcesAdminDelete;

class MarketResearchResource extends Resource
{
    use EnforcesAdminDelete;
    protected static ?string $model = MarketResearch::class;
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationGroup = 'Strategy';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                Forms\Components\Select::make('category')->options([
                    'trend' => 'Market Trend', 'competitor' => 'Competitor Analysis',
                    'opportunity' => 'Market Opportunity', 'industry_report' => 'Industry Report',
                ])->default('trend')->required(),
                Forms\Components\TextInput::make('source')->maxLength(255),
                Forms\Components\DatePicker::make('research_date'),
                Forms\Components\Textarea::make('summary')->rows(3)->columnSpanFull(),
                Forms\Components\RichEditor::make('findings')->columnSpanFull()->required(),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('category')->badge()->color(fn ($state) => match ($state) {
                'trend' => 'info', 'competitor' => 'danger', 'opportunity' => 'success', 'industry_report' => 'primary', default => 'gray',
            }),
            Tables\Columns\TextColumn::make('source')->limit(30),
            Tables\Columns\TextColumn::make('research_date')->date()->sortable(),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('category')->options([
                'trend' => 'Market Trend', 'competitor' => 'Competitor Analysis',
                'opportunity' => 'Market Opportunity', 'industry_report' => 'Industry Report',
            ]),
        ])
        ->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Marketing\Resources\MarketResearchResource\RelationManagers\DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMarketResearch::route('/'),
            'create' => Pages\CreateMarketResearch::route('/create'),
            'view'   => Pages\ViewMarketResearch::route('/{record}'),
            'edit'   => Pages\EditMarketResearch::route('/{record}/edit'),
        ];
    }
}
