<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SubscriptionAccount extends Model { protected $fillable=['owner_id','billing_company_id','trial_started_at','trial_ends_at','trial_credited_at']; protected $casts=['trial_started_at'=>'datetime','trial_ends_at'=>'datetime','trial_credited_at'=>'datetime']; public function owner(){return $this->belongsTo(User::class,'owner_id');} public function billingCompany(){return $this->belongsTo(Company::class,'billing_company_id');} public function companies(){return $this->hasMany(Company::class,'subscription_account_id');} public function subscriptions(){return $this->hasMany(Subscription::class);} }
