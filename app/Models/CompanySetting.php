<?php

namespace App\Models;

use App\Services\CompanyContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    use HasFactory;
    protected $fillable = ['name','email','adress','number1','number2','message','logo','description','ecommerce_active','sms_count','whatsapp_count','sale_whatsapp_enabled','sale_sms_enabled','inventory_whatsapp_enabled','inventory_sms_enabled'];

    protected static function booted(): void
    {
        static::addGlobalScope('active_company', function (Builder $builder) {
            $context = app(CompanyContext::class);
            if ($context->isResolved()) {
                $builder->where($builder->getModel()->getTable() . '.id', $context->getCompanyId());
            }
        });
    }

    public function managers()
    {
        return $this->hasMany(EcommerceManager::class, 'company_id');
    }

    public function managerUsers()
    {
        return $this->belongsToMany(User::class, 'ecommerce_managers', 'company_id', 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'company_id');
    }
}
