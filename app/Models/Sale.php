<?php

namespace App\Models;

use App\Models\SaleDetail;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use BelongsToCompany, HasFactory;
    protected $fillable = ['company_id','code','received_amount','total_amount', 'remaining_amount', 'total_profit','cashier','code_promo','discount','tax_amount','amount_init','client_id'];

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
