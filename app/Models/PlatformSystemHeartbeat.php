<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSystemHeartbeat extends Model
{
    protected $fillable = ['key', 'last_seen_at', 'metadata'];
    protected $casts = ['last_seen_at' => 'datetime', 'metadata' => 'array'];
}
