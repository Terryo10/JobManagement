<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserSkillResource\Pages;
use App\Models\UserSkill;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserSkillResource extends Resource
{
    protected static ?string $model = UserSkill::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'HR';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')->relationship('user', 'name')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('skill')->required()->maxLength(100),
            Forms\Components\Select::make('level')->options([1 => 'Basic', 2 => 'Intermediate', 3 => 'Expert'])->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('user.name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('skill')->searchable(),
            Tables\Columns\TextColumn::make('level')->formatStateUsing(fn ($state) => match ($state) { 1 => 'Basic', 2 => 'Intermediate', 3 => 'Expert', default => $state })->badge(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([Tables\Filters\SelectFilter::make('level')->options([1 => 'Basic', 2 => 'Intermediate', 3 => 'Expert'])])
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
            'index'  => Pages\ListUserSkills::route('/'),
            'create' => Pages\CreateUserSkill::route('/create'),
            'edit'   => Pages\EditUserSkill::route('/{record}/edit'),
        ];
    }
}
