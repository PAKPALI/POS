<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerPayoutEvent extends Model
{
    protected $fillable = ['partner_withdrawal_id', 'event_id', 'event_type', 'provider_status', 'payload_hash', 'payload', 'received_at', 'processed_at', 'processing_error'];
    protected $casts = ['payload' => 'array', 'received_at' => 'datetime', 'processed_at' => 'datetime'];
    public function withdrawal() { return $this->belongsTo(PartnerWithdrawal::class, 'partner_withdrawal_id'); }
}
