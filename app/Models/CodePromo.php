<?php

namespace App\Models;
use App\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodePromo extends Model
{
    use BelongsToCompany, HasFactory;
    protected $fillable = ['company_id','name','code','created_by','percents','status','comments','qr_code'];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
