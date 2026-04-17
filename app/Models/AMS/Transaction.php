<?php

namespace App\Models\AMS;

use App\Models\AMS\CashAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'from_cash_id',
        'to_cash_id',
        'type',
        'amount',
        'description',
        'created_by'
    ];

    public function fromCash()
    {
        return $this->belongsTo(CashAccount::class, 'from_cash_id');
    }

    public function toCash()
    {
        return $this->belongsTo(CashAccount::class, 'to_cash_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}