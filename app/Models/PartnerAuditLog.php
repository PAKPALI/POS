<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerAuditLog extends Model
{
    protected $fillable = ['partner_id', 'action', 'target_type', 'target_id', 'old_values', 'new_values', 'reason', 'ip_address', 'user_agent_hash', 'result'];

    protected $casts = ['old_values' => 'array', 'new_values' => 'array'];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
