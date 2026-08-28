<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformAlertSetting extends Model
{
    protected $fillable = ['enabled', 'recipient_admin_ids', 'failed_jobs_threshold', 'queue_age_minutes',
        'blocked_payment_minutes', 'delivery_failure_percent', 'delivery_minimum_volume', 'cooldown_minutes'];

    protected $casts = ['enabled' => 'boolean', 'recipient_admin_ids' => 'array'];

    public static function current(): self
    {
        return static::firstOrCreate([], ['recipient_admin_ids' => []]);
    }
}
