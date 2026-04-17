<?php

namespace App\Models\AMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'default_cash_id',
        'tax_cash_id',
        'default_tax'
    ];

    public function cash()
    {
        return $this->belongsTo(CashAccount::class, 'default_cash_id');
    }

    public function taxCash()
    {
        return $this->belongsTo(CashAccount::class, 'tax_cash_id');
    }
}
