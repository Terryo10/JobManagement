<?php

namespace App\Filament\Staff\Resources\DesignBriefResource\Pages;

use App\Filament\Staff\Resources\DesignBriefResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateDesignBrief extends CreateRecord
{
    protected static string $resource = DesignBriefResource::class;

    protected array $attachments = [];
    protected array $attachmentNames = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

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
