<?php

namespace App\Models;

use App\Models\Sale;
use App\Models\Product;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaleDetail extends Model
{
    use BelongsToCompany, HasFactory;
    protected $fillable = ['company_id', 'sale_id', 'product_id', 'quantity', 'unit_price', 'total_price', 'profit'];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
