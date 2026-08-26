<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class QuotaPayment extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'user_id', 'transaction_id', 'idempotency_key', 'kpp_reference',
        'event_id', 'sms_quantity', 'whatsapp_quantity', 'amount', 'currency', 'status',
        'checkout_url', 'failure_reason', 'expires_at', 'paid_at', 'failed_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
    ];
}
