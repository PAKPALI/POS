<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerExport extends Model
{
    protected $fillable = [
        'partner_id', 'type', 'filters', 'status', 'path', 'expires_at',
        'requested_at', 'completed_at', 'failed_at', 'failure_reason',
    ];

    protected $casts = [
        'filters' => 'array',
        'expires_at' => 'datetime',
        'requested_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
