<?php

namespace App\Filament\Admin\Resources\PrazSubmissionResource\RelationManagers;

use App\Filament\Shared\RelationManagers\BaseDocumentsRelationManager;
use App\Support\DocumentFileTypes;

class DocumentsRelationManager extends BaseDocumentsRelationManager
{
    protected string $storageDirectory = 'documents/praz-submissions';
    protected array $allowedTypes = [];
    protected int $maxFileSizeKB = DocumentFileTypes::SIZE_50MB;
}
