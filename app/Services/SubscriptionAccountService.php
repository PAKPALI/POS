<?php
namespace App\Services;
use App\Models\{Company,SubscriptionAccount,SubscriptionPlan,Subscription};
use Illuminate\Support\Facades\DB;
class SubscriptionAccountService
{
    public function ensureFor(Company $company, int $ownerId): SubscriptionAccount
    {
        return DB::transaction(function () use ($company,$ownerId) {
            if ($company->subscription_account_id) return SubscriptionAccount::findOrFail($company->subscription_account_id);
            $account=SubscriptionAccount::firstOrCreate(['owner_id'=>$ownerId],['billing_company_id'=>$company->id]);
            if (!$account->billing_company_id) $account->update(['billing_company_id'=>$company->id]);
            $company->update(['subscription_account_id'=>$account->id]);
            if (!$account->trial_started_at) {
                $plan=SubscriptionPlan::where('key','trial')->firstOrFail(); $start=now(); $end=$start->copy()->addDays($plan->trial_days);
                $account->update(['trial_started_at'=>$start,'trial_ends_at'=>$end]);
                Subscription::create(['subscription_account_id'=>$account->id,'subscription_plan_id'=>$plan->id,'status'=>'trial','billing_period'=>'trial','starts_at'=>$start,'ends_at'=>$end,'snapshot'=>$this->snapshot($plan)]);
                Company::withoutGlobalScopes()->whereKey($account->billing_company_id)->increment('sms_count',$plan->sms_quota);
                Company::withoutGlobalScopes()->whereKey($account->billing_company_id)->increment('whatsapp_count',$plan->whatsapp_quota);
                $account->update(['trial_credited_at'=>now()]);
            }
            return $account;
        });
    }
    public function currentForCompany(Company $company): ?Subscription { if(!$company->subscription_account_id)return null; return Subscription::with('plan.features')->where('subscription_account_id',$company->subscription_account_id)->whereIn('status',['trial','active'])->orderByDesc('ends_at')->first(); }
    public function snapshot(SubscriptionPlan $p): array { return ['key'=>$p->key,'name'=>$p->name,'rank'=>$p->rank,'monthly_price'=>$p->monthly_price,'annual_price'=>$p->annual_price,'currency'=>$p->currency,'company_limit'=>$p->company_limit,'user_limit'=>$p->user_limit,'product_limit'=>$p->product_limit,'sms_quota'=>$p->sms_quota,'whatsapp_quota'=>$p->whatsapp_quota,'features'=>$p->features()->pluck('enabled','feature_key')->all(),'version'=>$p->version]; }
}
