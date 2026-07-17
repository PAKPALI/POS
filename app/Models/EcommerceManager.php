<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceManager extends Model
{
    use HasFactory;

    protected $fillable = ['company_id', 'user_id'];

    public function company()
    {
        return $this->belongsTo(CompanySetting::class, 'company_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
