<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SubscriptionEvent extends Model { protected $fillable=['subscription_account_id','subscription_id','event_key','payload','occurred_at']; protected $casts=['payload'=>'array','occurred_at'=>'datetime']; }
