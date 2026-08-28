<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformAuditLog extends Model
{
    protected $fillable = [
        'platform_admin_id', 'action', 'target_type', 'target_id', 'old_values',
        'new_values', 'reason', 'ip_address', 'user_agent', 'result',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function admin()
    {
        return $this->belongsTo(PlatformAdmin::class, 'platform_admin_id');
    }
}
