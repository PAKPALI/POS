<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerCommission extends Model
{
    protected $fillable = [
        'partner_id', 'partner_attribution_id', 'subscription_payment_id', 'type',
        'gross_amount', 'discount_amount', 'net_paid_amount', 'commission_rate_bps',
        'commission_amount', 'currency', 'status', 'available_at', 'reserved_at', 'paid_at',
        'reversed_at', 'rule_version', 'calculation_snapshot',
    ];

    protected $casts = [
        'gross_amount' => 'integer',
        'discount_amount' => 'integer',
        'net_paid_amount' => 'integer',
        'commission_rate_bps' => 'integer',
        'commission_amount' => 'integer',
        'available_at' => 'datetime',
        'reserved_at' => 'datetime',
        'paid_at' => 'datetime',
        'reversed_at' => 'datetime',
        'calculation_snapshot' => 'array',
    ];

    public function partner() { return $this->belongsTo(Partner::class); }
    public function attribution() { return $this->belongsTo(PartnerAttribution::class, 'partner_attribution_id'); }
    public function subscriptionPayment() { return $this->belongsTo(SubscriptionPayment::class); }
}
