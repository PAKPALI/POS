<?php

namespace App\Models;

use App\Models\MenuProduct;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['category_id','name','created_by','qte','price','purchase_price','margin','profit','image','status','email','type'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function MenuProducts()
    {
        return $this->hasMany(MenuProduct::class, 'menu_id', 'id');
    }
}
