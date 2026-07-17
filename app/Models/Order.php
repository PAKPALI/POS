<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'code', 'customer_name', 'customer_phone',
        'customer_email', 'customer_address', 'notes',
        'subtotal', 'tax', 'total', 'status'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function company()
    {
        return $this->belongsTo(CompanySetting::class, 'company_id');
    }
}
