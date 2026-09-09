<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerPromoCode extends Model
{
    protected $fillable = ['partner_id', 'code', 'normalized_code', 'status', 'is_primary', 'activated_at', 'disabled_at', 'last_used_at'];

    protected $casts = ['is_primary' => 'boolean', 'activated_at' => 'datetime', 'disabled_at' => 'datetime', 'last_used_at' => 'datetime'];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function checkoutIntents()
    {
        return $this->hasMany(PartnerCheckoutIntent::class);
    }
}
