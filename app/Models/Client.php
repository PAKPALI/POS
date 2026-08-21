<?php

namespace App\Models;
use App\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = ['company_id','name','created_by','status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
