<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Billboard extends Model
{
    protected $fillable = [
        'name', 'location_description', 'latitude', 'longitude',
        'size', 'type', 'status', 'monthly_rate', 'next_maintenance_date', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'next_maintenance_date' => 'date',
            'monthly_rate' => 'decimal:2',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }
}
