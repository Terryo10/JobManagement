<?php

namespace App\Filament\Shared\RelationManagers;

use App\Support\DocumentFileTypes;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

abstract class BaseDocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';
    protected static ?string $title = 'Documents';

    /** Storage directory within the contabo bucket */
    protected string $storageDirectory = 'documents/general';

    /** Allowed MIME types / extensions — defaults to all types */
    protected array $allowedTypes = [];

    /** Max file size in KB — defaults to 50 MB */
    protected int $maxFileSizeKB = DocumentFileTypes::SIZE_50MB;

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\FileUpload::make('file_path')
                ->label('File(s)')
                ->disk('contabo')
                ->directory($this->storageDirectory)
                ->visibility('private')
                ->acceptedFileTypes($this->allowedTypes ?: DocumentFileTypes::all())
                ->maxSize($this->maxFileSizeKB)
                ->multiple()
                ->reorderable()
                ->required()
                ->columnSpanFull(),
            Forms\Components\TagsInput::make('tags')->columnSpanFull(),
            Forms\Components\Textarea::make('notes')->rows(2)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('mime_type')->label('Type')->badge(),
                Tables\Columns\TextColumn::make('uploadedBy.name')->label('Uploaded By'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->label('Uploaded'),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data, string $model): \Illuminate\Database\Eloquent\Model {
                        $owner     = $this->getOwnerRecord();
                        $filePaths = (array) ($data['file_path'] ?? []);
                        $tags      = $data['tags'] ?? [];
                        $notes     = $data['notes'] ?? null;
                        $first     = null;

                        foreach ($filePaths as $path) {
                            $ext  = pathinfo($path, PATHINFO_EXTENSION);
                            $name = count($filePaths) === 1
                                ? ($data['name'] ?? pathinfo($path, PATHINFO_FILENAME))
                                : pathinfo($path, PATHINFO_FILENAME);

                            $doc = $owner->documents()->create([
                                'name'        => $name,
                                'file_path'   => $path,
                                'mime_type'   => $ext,
                                'tags'        => $tags,
                                'notes'       => $notes,
                                'uploaded_by' => auth()->id(),
                            ]);

                            $first ??= $doc;
                        }

                        // Fallback (should never hit in normal use)
                        return $first ?? $owner->documents()->make([
                            'name'        => $data['name'] ?? 'Untitled',
                            'file_path'   => '',
                            'mime_type'   => '',
                            'uploaded_by' => auth()->id(),
                        ]);
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record) => Storage::disk('contabo')->temporaryUrl($record->file_path, now()->addHour()))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                \App\Filament\Shared\Actions\RequestDeletionTableAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }
}
