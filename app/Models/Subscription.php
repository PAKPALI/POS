<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Subscription extends Model { protected $fillable=['subscription_account_id','subscription_plan_id','status','billing_period','duration_months','starts_at','ends_at','snapshot']; protected $casts=['duration_months'=>'integer','starts_at'=>'datetime','ends_at'=>'datetime','snapshot'=>'array']; public function plan(){return $this->belongsTo(SubscriptionPlan::class,'subscription_plan_id');} public function subscriptionAccount(){return $this->belongsTo(SubscriptionAccount::class,'subscription_account_id');} }
