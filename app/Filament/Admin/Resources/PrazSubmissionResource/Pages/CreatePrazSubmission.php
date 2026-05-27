<?php

namespace App\Filament\Admin\Resources\PrazSubmissionResource\Pages;

use App\Filament\Admin\Resources\PrazSubmissionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreatePrazSubmission extends CreateRecord
{
    protected static string $resource = PrazSubmissionResource::class;

    protected array $attachments = [];
    protected array $attachmentNames = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['prepared_by'] = auth()->id();

        $this->attachments = $data['attachments'] ?? [];
        $this->attachmentNames = $data['attachment_names'] ?? [];
        unset($data['attachments'], $data['attachment_names']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        foreach ($this->attachments as $key => $path) {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            $name = $this->attachmentNames[$key] ?? pathinfo($path, PATHINFO_FILENAME);

            $size = null;
            try {
                $size = Storage::disk('contabo')->size($path);
            } catch (\Throwable $e) {
                // Ignore size if retrieval fails
            }

            $record->documents()->create([
                'name'        => $name,
                'file_path'   => $path,
                'mime_type'   => $ext,
                'size'        => $size,
                'uploaded_by' => auth()->id(),
            ]);
        }
    }
}
