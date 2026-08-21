<?php

namespace App\Models;
use App\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = ['company_id','name','contact','phone','whatsapp','created_by','status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }
}
