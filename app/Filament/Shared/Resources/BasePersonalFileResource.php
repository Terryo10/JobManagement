<?php

namespace App\Filament\Shared\Resources;

use App\Models\PersonalFile;
use App\Support\DocumentFileTypes;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

abstract class BasePersonalFileResource extends Resource
{
    protected static ?string $model = PersonalFile::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationIcon = 'heroicon-o-folder';
    protected static ?string $navigationLabel = 'My Files';
    protected static ?string $pluralModelLabel = 'My Files';
    protected static ?string $modelLabel = 'File';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('file_path')
                    ->label('File')
                    ->required()
                    ->disk('contabo')
                    ->directory('files/personal/' . auth()->id())
                    ->visibility('private')
                    ->acceptedFileTypes(DocumentFileTypes::all())
                    ->maxSize(DocumentFileTypes::SIZE_1GB)
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('is_shared')
                    ->label('Share with organisation')
                    ->helperText('When enabled, all team members can view and download this file.')
                    ->default(false),

                Forms\Components\TagsInput::make('tags')
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('description')
                    ->rows(2)
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-document'),

                Tables\Columns\TextColumn::make('mime_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => strtoupper((string) $state)),

                Tables\Columns\TextColumn::make('size')
                    ->label('Size')
                    ->formatStateUsing(fn ($state) => self::formatBytes((int) $state))
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_shared')
                    ->label('Sharing')
                    ->boolean()
                    ->trueIcon('heroicon-o-globe-alt')
                    ->falseIcon('heroicon-o-lock-closed')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('owner.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (PersonalFile $record) =>
                        Storage::disk('contabo')->temporaryUrl($record->file_path, now()->addHour())
                    )
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('toggle_share')
                    ->label(fn (PersonalFile $record) => $record->is_shared ? 'Make Private' : 'Share')
                    ->icon(fn (PersonalFile $record) => $record->is_shared ? 'heroicon-o-lock-closed' : 'heroicon-o-globe-alt')
                    ->color(fn (PersonalFile $record) => $record->is_shared ? 'warning' : 'success')
                    ->visible(fn (PersonalFile $record) => $record->user_id === auth()->id())
                    ->requiresConfirmation()
                    ->action(fn (PersonalFile $record) => $record->update(['is_shared' => ! $record->is_shared])),

                Tables\Actions\EditAction::make()
                    ->visible(fn (PersonalFile $record) => $record->user_id === auth()->id()),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn (PersonalFile $record) => $record->user_id === auth()->id()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canEdit(Model $record): bool
    {
        return $record->user_id === auth()->id();
    }

    public static function canDelete(Model $record): bool
    {
        return $record->user_id === auth()->id();
    }

    private static function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1_048_576) {
            return round($bytes / 1024, 1) . ' KB';
        }
        if ($bytes < 1_073_741_824) {
            return round($bytes / 1_048_576, 1) . ' MB';
        }

        return round($bytes / 1_073_741_824, 2) . ' GB';
    }
}
