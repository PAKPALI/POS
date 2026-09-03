<?php
namespace App\Services;
use App\Exceptions\SubscriptionLimitReached;
use App\Models\{Company,CompanyUser,Product,Subscription,SubscriptionAccount};
class EntitlementService
{
    public function __construct(private SubscriptionAccountService $accounts, private PlatformConfigurationService $configuration){}
    public function enforcementEnabled(): bool{return $this->configuration->boolean('subscriptions.enforcement_enabled',false);}
    public function current(Company $company): ?Subscription{return $this->accounts->currentForCompany($company);}
    public function active(Company $company): bool { $s=$this->current($company); return $s && $s->ends_at->isFuture(); }
    public function readOnly(Company $company): bool{return $this->enforcementEnabled() && !$this->active($company);}
    public function feature(Company $company,string $feature): bool { if(!$this->enforcementEnabled())return true; $s=$this->current($company); if(!$s||!$s->ends_at->isFuture())return false; return (bool)($s->snapshot['features'][$feature] ?? false); }
    public function usage(Company $company): array { $account=$company->subscriptionAccount; if(!$account)return []; $companyIds=$account->companies()->where('status','active')->pluck('id'); return ['companies'=>$companyIds->count(),'users'=>CompanyUser::whereIn('company_id',$companyIds)->where('status','active')->distinct('user_id')->count('user_id'),'products'=>Product::withoutCompanyScope()->where('company_id',$company->id)->where('status',true)->count()]; }
    public function canAdd(Company $company,string $resource): bool { if(!$this->enforcementEnabled())return true; $s=$this->current($company); if(!$s||!$s->ends_at->isFuture())return false; $usage=$this->usage($company); $map=['company'=>'companies','user'=>'users','product'=>'products']; $key=$map[$resource]??null; if(!$key)return false; $limit=$key==='companies'?'company_limit':($key==='users'?'user_limit':'product_limit'); return $usage[$key] < (int)($s->snapshot[$limit]??0); }
    public function canAddUser(Company $company,int $userId): bool { if(!$this->enforcementEnabled())return true; $account=$company->subscriptionAccount; if(!$account)return false; $ids=$account->companies()->pluck('id'); if(CompanyUser::whereIn('company_id',$ids)->where('user_id',$userId)->where('status','active')->exists())return true; return $this->canAdd($company,'user'); }

    /** Appelé dans la même transaction que l’écriture afin de sérialiser les limites du compte. */
    public function assertCanAdd(Company $company, string $resource): void
    {
        if (!$this->enforcementEnabled()) return;
        $lockedCompany = $this->lockAccountFor($company);
        if (!$this->canAdd($lockedCompany, $resource)) {
            throw new SubscriptionLimitReached('La limite de votre plan est atteinte.');
        }
    }

    /** L’utilisateur déjà membre du compte ne consomme pas une place supplémentaire. */
    public function assertCanAddUser(Company $company, int $userId): void
    {
        if (!$this->enforcementEnabled()) return;
        $lockedCompany = $this->lockAccountFor($company);
        $account = $lockedCompany->subscriptionAccount;
        $companyIds = $account->companies()->pluck('id');
        if (CompanyUser::whereIn('company_id', $companyIds)->where('user_id', $userId)->where('status', 'active')->exists()) return;
        if (!$this->canAdd($lockedCompany, 'user')) {
            throw new SubscriptionLimitReached('La limite d’utilisateurs de votre plan est atteinte.');
        }
    }

    private function lockAccountFor(Company $company): Company
    {
        $lockedCompany = Company::withoutGlobalScopes()->findOrFail($company->id);
        $account = SubscriptionAccount::lockForUpdate()->find($lockedCompany->subscription_account_id);
        if (!$account) throw new SubscriptionLimitReached('Aucun compte d’abonnement n’est associé à cette compagnie.');
        $lockedCompany->setRelation('subscriptionAccount', $account);

        return $lockedCompany;
    }
}
