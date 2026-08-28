<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformOperationalAlert extends Model
{
    protected $fillable = ['type', 'fingerprint', 'severity', 'status', 'title', 'message', 'context',
        'detected_at', 'last_detected_at', 'last_notified_at', 'acknowledged_by', 'acknowledged_at',
        'resolved_by', 'resolved_at'];
    protected $casts = ['context' => 'array', 'detected_at' => 'datetime', 'last_detected_at' => 'datetime',
        'last_notified_at' => 'datetime', 'acknowledged_at' => 'datetime', 'resolved_at' => 'datetime'];
}
