<?php

namespace App\Models\AMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashAccount extends Model
{
    use HasFactory;

    protected $table = 'cash_accounts';

    protected $fillable = [
        'name',
        'code',
        'balance',
        'currency',
        'is_default',
        'status',
        'description',
        'created_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}