<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationRule extends Model
{
    protected $fillable = ['rule_key', 'label', 'value', 'description', 'applies_to_role'];
}
