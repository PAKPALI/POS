<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyResetRequest extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    protected $hidden = ['code_hash', 'session_hash'];
    protected $casts = [
        'selection' => 'array', 'summary' => 'array', 'expires_at' => 'datetime',
        'completed_at' => 'datetime', 'notification_sent_at' => 'datetime', 'terms_accepted_at' => 'datetime',
    ];
}
