<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class PersonalFile extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (PersonalFile $file) {
            if (empty($file->user_id)) {
                $file->user_id = auth()->id();
            }

            if (empty($file->mime_type) && $file->file_path) {
                $file->mime_type = pathinfo($file->file_path, PATHINFO_EXTENSION);
            }

            if (empty($file->size) && $file->file_path) {
                try {
                    $file->size = Storage::disk('contabo')->size($file->file_path);
                } catch (\Throwable) {
                    $file->size = 0;
                }
            }
        });
    }

    protected $fillable = [
        'user_id',
        'name',
        'file_path',
        'mime_type',
        'size',
        'is_shared',
        'tags',
        'description',
    ];

    protected $casts = [
        'is_shared' => 'boolean',
        'tags'      => 'array',
        'size'      => 'integer',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sharedWith(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'personal_file_shares', 'personal_file_id', 'user_id');
    }

}
