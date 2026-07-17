<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    use HasFactory;
    protected $fillable = ['name','email','adress','number1','number2','message','logo','description','ecommerce_active','sms_count','whatsapp_count'];

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
