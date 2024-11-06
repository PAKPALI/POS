<?php

namespace App\Models;

use App\Models\SaleDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;
    protected $fillable = ['code','total_amount','total_profit','cashier'];

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }
}
