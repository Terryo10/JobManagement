<?php

namespace App\Filament\Marketing\Resources\QuotationResource\RelationManagers;

use App\Filament\Shared\RelationManagers\BaseDocumentsRelationManager;
use App\Support\DocumentFileTypes;

class DocumentsRelationManager extends BaseDocumentsRelationManager
{
    protected string $storageDirectory = 'documents/quotations';
    protected array $allowedTypes = [];
    protected int $maxFileSizeKB = DocumentFileTypes::SIZE_20MB;
}
