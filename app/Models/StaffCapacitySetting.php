<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffCapacitySetting extends Model
{
    protected $fillable = ['role_name', 'max_concurrent_tasks', 'max_weekly_hours'];
}
