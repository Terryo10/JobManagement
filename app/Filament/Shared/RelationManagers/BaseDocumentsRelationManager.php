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
                ->label('File')
                ->disk('contabo')
                ->directory($this->storageDirectory)
                ->visibility('private')
                ->acceptedFileTypes($this->allowedTypes ?: DocumentFileTypes::all())
                ->maxSize($this->maxFileSizeKB)
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
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['uploaded_by'] = auth()->id();
                        if (isset($data['file_path'])) {
                            $data['mime_type'] = pathinfo($data['file_path'], PATHINFO_EXTENSION);
                        }
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record) => Storage::disk('contabo')->temporaryUrl($record->file_path, now()->addHour()))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }
}
