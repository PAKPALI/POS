<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuProduct extends Model
{
    use HasFactory;
    protected $fillable = ['menu_id','product_id','quantity'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
