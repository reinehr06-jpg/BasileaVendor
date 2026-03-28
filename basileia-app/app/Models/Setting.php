<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key, optionally providing a default.
     * Uses cache to avoid repeated DB hits.
     * Handles boolean values correctly.
     */
    public static function get(string $key, $default = null)
    {
        return Cache::rememberForever("setting_{$key}", function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            if (!$setting) {
                return $default;
            }
            
            $value = $setting->value;
            
            if ($value === '1' || $value === 'true') {
                return true;
            }
            if ($value === '0' || $value === 'false') {
                return false;
            }
            
            return $value;
        });
    }

    /**
     * Set a setting value by key. Updates if exists, creates if not.
     * Overwrites cache.
     */
    public static function set(string $key, $value)
    {
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }
        
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        Cache::put("setting_{$key}", $value);
    }

    /**
     * Clear all settings cache. Useful after bulk updates or when cache becomes stale.
     */
    public static function clearAllCache(): void
    {
        $allSettings = self::pluck('key')->all();
        foreach ($allSettings as $key) {
            Cache::forget("setting_{$key}");
        }
    }
}
