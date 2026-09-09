<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerAttribution extends Model
{
    protected $fillable = [
        'subscription_account_id', 'partner_id', 'partner_promo_code_id', 'first_subscription_payment_id',
        'acquisition_rank', 'commission_rate_bps', 'rule_version', 'attributed_at', 'status',
        'reversed_at', 'reversal_reason',
    ];

    protected $casts = [
        'acquisition_rank' => 'integer', 'commission_rate_bps' => 'integer',
        'attributed_at' => 'datetime', 'reversed_at' => 'datetime',
    ];

    public function subscriptionAccount() { return $this->belongsTo(SubscriptionAccount::class); }
    public function partner() { return $this->belongsTo(Partner::class); }
    public function promoCode() { return $this->belongsTo(PartnerPromoCode::class, 'partner_promo_code_id'); }
    public function firstPayment() { return $this->belongsTo(SubscriptionPayment::class, 'first_subscription_payment_id'); }
    public function commissions() { return $this->hasMany(PartnerCommission::class, 'partner_attribution_id'); }
}
