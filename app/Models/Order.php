<?php

namespace App\Models;
use App\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'company_id', 'code', 'customer_name', 'customer_phone',
        'customer_email', 'customer_address', 'delivery_location_url',
        'delivery_latitude', 'delivery_longitude', 'notes',
        'subtotal', 'tax', 'total', 'status', 'sale_id',
        'converted_at', 'converted_by', 'cancelled_at', 'cancelled_by', 'cancellation_reason',
    ];

    protected $casts = [
        'converted_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'delivery_latitude' => 'float',
        'delivery_longitude' => 'float',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function company()
    {
        return $this->belongsTo(CompanySetting::class, 'company_id');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function convertedBy()
    {
        return $this->belongsTo(User::class, 'converted_by');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
