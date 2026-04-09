<?php

namespace App\Filament\Shared\RelationManagers;

use App\Models\Document;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

abstract class BaseCommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';
    protected static ?string $title = 'Comments';
    protected static ?string $icon = 'heroicon-o-chat-bubble-left-right';

    /** Whether to show the "Internal Only" visibility toggle */
    protected bool $showVisibilityToggle = true;

    /** Admins can edit/delete any comment; others can only manage their own */
    protected bool $canManageAllComments = false;

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\RichEditor::make('body')
                ->label('Comment')
                ->required()
                ->toolbarButtons([
                    'bold', 'italic', 'underline', 'strike',
                    'bulletList', 'orderedList',
                    'blockquote', 'codeBlock',
                    'h2', 'h3',
                    'link',
                    'undo', 'redo',
                ])
                ->columnSpanFull(),

            Forms\Components\Select::make('document_ids')
                ->label('Reference Documents')
                ->helperText('Attach or reference documents uploaded to this work order.')
                ->multiple()
                ->options(function () {
                    return $this->getOwnerRecord()
                        ->documents()
                        ->get()
                        ->mapWithKeys(fn (Document $doc) => [$doc->id => $doc->name]);
                })
                ->searchable()
                ->preload()
                ->columnSpanFull(),

            $this->showVisibilityToggle
                ? Forms\Components\Toggle::make('is_internal')
                    ->label('Internal (staff only)')
                    ->default(true)
                    ->helperText('When enabled, this comment is only visible to staff.')
                : Forms\Components\Hidden::make('is_internal')->default(true),
        ]);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\TextEntry::make('body')
                ->label('Comment')
                ->html()
                ->columnSpanFull(),
            Infolists\Components\TextEntry::make('document_ids')
                ->label('Referenced Documents')
                ->formatStateUsing(function ($state, $record) {
                    if (empty($state)) {
                        return '—';
                    }

                    $docs = Document::whereIn('id', (array) $state)->get();

                    if ($docs->isEmpty()) {
                        return '—';
                    }

                    $links = $docs->map(function (Document $doc) {
                        $url = Storage::disk('contabo')->temporaryUrl($doc->file_path, now()->addHour());
                        return '<a href="' . e($url) . '" target="_blank" class="text-primary-600 hover:underline">' . e($doc->name) . '</a>';
                    })->implode(', ');

                    return new HtmlString($links);
                })
                ->html()
                ->columnSpanFull()
                ->visible(fn ($record) => ! empty($record->document_ids)),
            Infolists\Components\TextEntry::make('user.name')
                ->label('Posted by'),
            Infolists\Components\TextEntry::make('created_at')
                ->label('Posted at')
                ->dateTime(),
            Infolists\Components\IconEntry::make('is_internal')
                ->label('Internal')
                ->boolean()
                ->visible(fn () => $this->showVisibilityToggle),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->columns([
                Tables\Columns\TextColumn::make('body')
                    ->label('Comment')
                    ->html()
                    ->limit(120)
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Author')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_internal')
                    ->label('Internal')
                    ->boolean()
                    ->visible(fn () => $this->showVisibilityToggle),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Posted')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Comment')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $this->canManageAllComments || $record->user_id === auth()->id()),
                \App\Filament\Shared\Actions\RequestDeletionTableAction::make()
                    ->visible(fn ($record) => $this->canManageAllComments || $record->user_id === auth()->id()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No comments yet')
            ->emptyStateDescription('Be the first to leave a comment on this work order.')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right');
    }
}
