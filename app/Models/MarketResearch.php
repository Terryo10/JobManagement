<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class MarketResearch extends Model
{
    use LogsActivity;
    protected $table = 'market_research';

    protected $fillable = [
        'title', 'category', 'summary', 'source',
        'findings', 'researched_by', 'research_date',
    ];

    protected function casts(): array
    {
        return [
            'research_date' => 'date',
        ];
    }

    public function researchedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'researched_by');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
