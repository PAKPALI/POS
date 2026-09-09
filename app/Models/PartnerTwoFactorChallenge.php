<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerTwoFactorChallenge extends Model
{
    protected $fillable = ['partner_id', 'purpose', 'code_hash', 'expires_at', 'attempts', 'max_attempts', 'consumed_at', 'request_ip', 'user_agent_hash'];

    protected $hidden = ['code_hash'];

    protected $casts = ['expires_at' => 'datetime', 'consumed_at' => 'datetime'];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
