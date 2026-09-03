<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PlanFeature extends Model { protected $fillable=['subscription_plan_id','feature_key','enabled']; protected $casts=['enabled'=>'boolean']; }
