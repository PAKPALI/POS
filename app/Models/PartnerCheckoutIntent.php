<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerCheckoutIntent extends Model
{
    protected $fillable = [
        'subscription_payment_id', 'subscription_account_id', 'partner_id', 'partner_promo_code_id',
        'code', 'rule_version', 'discount_bps', 'candidate_commission_bps', 'gross_amount',
        'discount_amount', 'net_amount', 'currency', 'status', 'expires_at', 'settled_at', 'failure_reason',
    ];

    protected $casts = [
        'discount_bps' => 'integer', 'candidate_commission_bps' => 'integer',
        'gross_amount' => 'integer', 'discount_amount' => 'integer', 'net_amount' => 'integer',
        'expires_at' => 'datetime', 'settled_at' => 'datetime',
    ];

    public function payment() { return $this->belongsTo(SubscriptionPayment::class, 'subscription_payment_id'); }
    public function subscriptionAccount() { return $this->belongsTo(SubscriptionAccount::class); }
    public function partner() { return $this->belongsTo(Partner::class); }
    public function promoCode() { return $this->belongsTo(PartnerPromoCode::class, 'partner_promo_code_id'); }
}
