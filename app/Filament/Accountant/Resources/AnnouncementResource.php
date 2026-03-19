<?php

namespace App\Filament\Accountant\Resources;

use App\Filament\Accountant\Resources\AnnouncementResource\Pages;
use App\Filament\Shared\RelationManagers\AnnouncementCommentsRelationManager;
use App\Models\Announcement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationLabel = 'Announcements';
    protected static ?string $navigationGroup = null;
    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->placeholder('Announcement title…'),
                Forms\Components\Toggle::make('is_pinned')
                    ->label('Pin to top of announcements')
                    ->helperText('Pinned announcements always appear first.')
                    ->inline(false),
                Forms\Components\RichEditor::make('body')
                    ->required()
                    ->columnSpanFull()
                    ->toolbarButtons([
                        'bold', 'italic', 'underline', 'strike',
                        'h2', 'h3',
                        'bulletList', 'orderedList',
                        'link',
                        'blockquote',
                        'codeBlock',
                        'undo', 'redo',
                    ]),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withCount('comments')
                ->with('author')
                ->orderByDesc('is_pinned')
                ->orderByDesc('created_at')
            )
            ->contentGrid(['md' => 2, 'xl' => 3])
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\TextColumn::make('title')
                            ->searchable()
                            ->weight(FontWeight::Bold)
                            ->grow(),
                        Tables\Columns\IconColumn::make('is_pinned')
                            ->boolean()
                            ->trueIcon('heroicon-s-bookmark')
                            ->falseIcon('')
                            ->trueColor('warning')
                            ->label(''),
                    ]),
                    Tables\Columns\TextColumn::make('excerpt')
                        ->color('gray')
                        ->wrap()
                        ->lineClamp(3),
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\TextColumn::make('author.name')
                            ->icon('heroicon-m-user-circle')
                            ->color('gray')
                            ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall),
                        Tables\Columns\TextColumn::make('created_at')
                            ->since()
                            ->color('gray')
                            ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall)
                            ->alignEnd(),
                    ]),
                    Tables\Columns\TextColumn::make('comments_count')
                        ->icon('heroicon-m-chat-bubble-left')
                        ->color('gray')
                        ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall)
                        ->label(''),
                ])->space(2),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_pinned')->label('Pinned'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->created_by === auth()->id()),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record->created_by === auth()->id()),
            ])
            ->paginated([12, 24, 48]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make()->schema([
                Infolists\Components\TextEntry::make('title')
                    ->hiddenLabel()
                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                    ->weight(FontWeight::Bold)
                    ->extraAttributes(['class' => 'pb-4 mb-4 border-b border-gray-100 dark:border-gray-800']),
                
                Infolists\Components\Grid::make(4)->schema([
                    Infolists\Components\IconEntry::make('is_pinned')
                        ->label('Pinned')
                        ->boolean(),
                    Infolists\Components\TextEntry::make('author.name')
                        ->label('Posted by')
                        ->icon('heroicon-m-user-circle'),
                    Infolists\Components\TextEntry::make('created_at')
                        ->label('Posted')
                        ->since(),
                    Infolists\Components\TextEntry::make('comments_count')
                        ->label('Comments')
                        ->state(fn ($record) => $record->comments()->count())
                        ->icon('heroicon-m-chat-bubble-left'),
                ]),
                
                Infolists\Components\TextEntry::make('body')
                    ->hiddenLabel()
                    ->html()
                    ->columnSpanFull()
                    ->prose()
                    ->extraAttributes(['class' => 'max-w-none']),
            ])->columnSpanFull(),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            AnnouncementCommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'view'   => Pages\ViewAnnouncement::route('/{record}'),
            'edit'   => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
