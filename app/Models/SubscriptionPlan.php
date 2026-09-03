<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SubscriptionPlan extends Model { protected $fillable=['key','name','rank','is_active','monthly_price','annual_price','currency','company_limit','user_limit','product_limit','sms_quota','whatsapp_quota','trial_days','version']; protected $casts=['is_active'=>'boolean']; public function features(){return $this->hasMany(PlanFeature::class);} }
