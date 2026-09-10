<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PlatformSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'updated_by'];

    protected static function booted(): void
    {
        static::saved(fn (self $setting) => Cache::forget('platform.config.'.$setting->key));
        static::deleted(fn (self $setting) => Cache::forget('platform.config.'.$setting->key));
    }
}
