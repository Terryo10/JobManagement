<?php

namespace App\Filament\Shared\Resources\Traits;

use Illuminate\Database\Eloquent\Model;

trait HandlesMultiplePersonalFiles
{
    protected function handleRecordCreation(array $data): Model
    {
        $filePaths = (array) ($data['file_path'] ?? []);
        $firstModel = null;
        
        // Grab the raw relationship data from the Livewire data array
        $sharedWith = $this->data['sharedWith'] ?? [];

        foreach ($filePaths as $index => $path) {
            $ext  = pathinfo($path, PATHINFO_EXTENSION);
            $name = count($filePaths) === 1
                ? ($data['name'] ?? pathinfo($path, PATHINFO_FILENAME))
                : pathinfo($path, PATHINFO_FILENAME);

            $fileModel = static::getModel()::create([
                'name'        => $name,
                'file_path'   => $path,
                'mime_type'   => $ext, // The boot method in PersonalFile parses it too, but we can set it here
                'tags'        => $data['tags'] ?? [],
                'description' => $data['description'] ?? null,
                'is_shared'   => $data['is_shared'] ?? false,
                'user_id'     => auth()->id(),
            ]);

            // Filament will naturally handle the relationship for the FIRST returned model via `afterCreate`
            // But we must manually attach the shared relationships to the other created models
            if ($index > 0 && ($data['is_shared'] ?? false) && !empty($sharedWith)) {
                $fileModel->sharedWith()->sync($sharedWith);
            }

            if ($index === 0) {
                $firstModel = $fileModel;
            }
        }

        return $firstModel ?? static::getModel()::create($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
