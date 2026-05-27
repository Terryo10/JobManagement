<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key with cache.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        return Cache::remember("system_setting.{$key}", 60, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value by key and bust cache.
     */
    public static function setValue(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value],
        );

        Cache::forget("system_setting.{$key}");
    }

    /**
     * Check if SMS is enabled platform-wide.
     */
    public static function smsEnabled(): bool
    {
        return (bool) static::getValue('sms_enabled', true);
    }
}
