<?php
namespace App\Services;
use App\Models\{Subscription,SubscriptionPayment,SubscriptionPlan}; use Illuminate\Support\Facades\DB; use Illuminate\Support\Str; use RuntimeException;
class SubscriptionCheckoutService
{
    public function __construct(private SubscriptionAccountService $accounts,private KprimePayService $kprime){}
    public function create(int $companyId,int $userId,string $planKey,int|string $duration): SubscriptionPayment
    {
        $months = is_string($duration) ? match ($duration) { 'annual' => 12, 'monthly' => 1, default => 0 } : (int) $duration;
        if ($months < 1 || $months > 12) throw new RuntimeException('La durée doit être comprise entre 1 et 12 mois.');
        return DB::transaction(function()use($companyId,$userId,$planKey,$months){
            $company=\App\Models\Company::withoutGlobalScopes()->lockForUpdate()->findOrFail($companyId); $account=$company->subscriptionAccount()->lockForUpdate()->firstOrFail();
            $plan=SubscriptionPlan::where('key',$planKey)->where('is_active',true)->with('features')->firstOrFail(); $current=Subscription::where('subscription_account_id',$account->id)->whereIn('status',['trial','active'])->orderByDesc('ends_at')->lockForUpdate()->first();
            $currentRank=(int)($current?->snapshot['rank'] ?? -1); if($current && (int)$plan->rank < $currentRank)throw new RuntimeException('Le passage à un plan inférieur est interdit.');
            $snapshot=$this->accounts->snapshot($plan); $snapshot['duration_months']=$months; $snapshot['discount_applied']=$months===12; $amount=$months===12?(int)$plan->annual_price:(int)$plan->monthly_price*$months; if($amount<1)throw new RuntimeException('Ce plan ne peut pas être payé.');
            $reference='SUB-'.$account->id.'-'.strtoupper(Str::random(16));
            return SubscriptionPayment::create(['subscription_account_id'=>$account->id,'subscription_id'=>$current?->id,'subscription_plan_id'=>$plan->id,'user_id'=>$userId,'transaction_id'=>$reference,'idempotency_key'=>'subscription-'.strtolower($reference),'operation'=>$current && $plan->rank>$currentRank?'upgrade':'renewal','billing_period'=>$months===12?'annual':'monthly','duration_months'=>$months,'amount_ht'=>$amount,'tax_amount'=>0,'amount'=>$amount,'currency'=>'XOF','snapshot'=>$snapshot,'status'=>'created']);
        });
    }
    public function checkout(SubscriptionPayment $payment): array
    {
        $data=$this->kprime->createSubscriptionCheckout($payment,route('subscriptions.return',['transaction_id'=>$payment->transaction_id]));
        $payment->update(['status'=>'pending','kpp_reference'=>$data['kpp_tx_reference'],'checkout_url'=>$data['checkout_url'],'expires_at'=>$data['expires_at']??now()->addHours(24)]); return $data;
    }
}
