<?php

namespace App\Filament\Admin\Resources\DesignBriefResource\RelationManagers;

use App\Filament\Shared\RelationManagers\BaseDocumentsRelationManager;
use App\Support\DocumentFileTypes;

class DocumentsRelationManager extends BaseDocumentsRelationManager
{
    protected string $storageDirectory = 'documents/design_briefs';
    protected array $allowedTypes = [];
    protected int $maxFileSizeKB = DocumentFileTypes::SIZE_1GB;
}
