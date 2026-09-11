<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformWithdrawalChallenge extends Model
{
    protected $fillable = ['platform_admin_id', 'purpose', 'code_hash', 'payload', 'attempts', 'expires_at', 'consumed_at', 'request_ip'];
    protected $hidden = ['code_hash'];
    protected $casts = ['payload' => 'array', 'expires_at' => 'datetime', 'consumed_at' => 'datetime'];
    public function admin() { return $this->belongsTo(PlatformAdmin::class, 'platform_admin_id'); }
}
