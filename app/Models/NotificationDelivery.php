<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationDelivery extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'event_type',
        'event_key',
        'category',
        'channel',
        'status',
        'attempts',
        'last_error',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'attempts' => 'integer',
    ];
}
