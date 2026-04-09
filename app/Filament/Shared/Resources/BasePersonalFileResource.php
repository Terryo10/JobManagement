<?php

namespace App\Filament\Shared\Resources;

use App\Models\PersonalFile;
use App\Models\User;
use App\Support\DocumentFileTypes;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Get;

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
                    ->label('File(s)')
                    ->required()
                    ->disk('contabo')
                    ->directory('files/personal/' . auth()->id())
                    ->visibility('private')
                    ->acceptedFileTypes(DocumentFileTypes::all())
                    ->maxSize(DocumentFileTypes::SIZE_1GB)
                    ->multiple()
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('is_shared')
                    ->label('Share with specific users')
                    ->helperText('When enabled, you can select specific users to share this file with.')
                    ->live(),

                Forms\Components\Select::make('sharedWith')
                    ->relationship(
                        'sharedWith',
                        'name',
                        fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('users.id', '!=', auth()->id()),
                    )
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->hidden(fn (Get $get): bool => ! $get('is_shared'))
                    ->columnSpanFull(),

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
