<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class QuotaPayment extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'user_id', 'transaction_id', 'idempotency_key', 'kpp_reference',
        'event_id', 'sms_quantity', 'sms_unit_price', 'sms_unit_cost', 'whatsapp_quantity', 'whatsapp_unit_price', 'whatsapp_unit_cost', 'amount', 'currency', 'status',
        'checkout_url', 'failure_reason', 'expires_at', 'paid_at', 'failed_at', 'administration_email_status',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
        'administration_email_status' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
