<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SubscriptionPayment extends Model
{
    protected $fillable = ['subscription_account_id','subscription_id','subscription_plan_id','user_id','transaction_id','idempotency_key','kpp_reference','event_id','operation','billing_period','duration_months','amount_ht','tax_amount','amount','gross_amount','discount_amount','partner_checkout_intent_id','promotion_rule_version','currency','snapshot','status','checkout_url','failure_reason','expires_at','paid_at','failed_at'];

    protected $casts = ['snapshot'=>'array','duration_months'=>'integer','gross_amount'=>'integer','discount_amount'=>'integer','expires_at'=>'datetime','paid_at'=>'datetime','failed_at'=>'datetime'];

    public function plan() { return $this->belongsTo(SubscriptionPlan::class,'subscription_plan_id'); }
    public function user() { return $this->belongsTo(User::class,'user_id'); }
    public function subscription() { return $this->belongsTo(Subscription::class,'subscription_id'); }
    public function subscriptionAccount() { return $this->belongsTo(SubscriptionAccount::class); }
    public function partnerCheckoutIntent() { return $this->hasOne(PartnerCheckoutIntent::class,'subscription_payment_id'); }
    public function partnerCommission() { return $this->hasOne(PartnerCommission::class, 'subscription_payment_id'); }

    public function getStatusVariantAttribute(): string
    {
        return match (strtolower((string) $this->status)) {
            'paid', 'succeeded', 'success', 'completed' => 'success',
            'pending', 'processing', 'initiated', 'created' => 'pending',
            'failed', 'expired', 'cancelled', 'canceled' => 'danger',
            default => 'neutral',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match (strtolower((string) $this->status)) {
            'paid', 'succeeded', 'success', 'completed' => 'Payé',
            'pending', 'processing', 'initiated' => 'En attente',
            'created' => 'Créé',
            'failed' => 'Échoué',
            'expired' => 'Expiré',
            'cancelled', 'canceled' => 'Annulé',
            default => ucfirst((string) $this->status ?: 'Inconnu'),
        };
    }
}
