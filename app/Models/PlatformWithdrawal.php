<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformWithdrawal extends Model
{
    public const OPEN_STATUSES = ['otp_verified', 'processing', 'unknown'];

    protected $fillable = ['platform_admin_id', 'platform_withdrawal_account_id', 'transaction_id', 'idempotency_key', 'kpp_reference', 'event_id', 'amount', 'estimated_fees', 'fees', 'currency', 'with_fees', 'status', 'provider_status', 'failure_reason', 'account_snapshot', 'funding_snapshot', 'requested_at', 'processing_at', 'succeeded_at', 'failed_at', 'unknown_at'];
    protected $casts = ['amount' => 'integer', 'estimated_fees' => 'integer', 'fees' => 'integer', 'with_fees' => 'boolean', 'account_snapshot' => 'array', 'funding_snapshot' => 'array', 'requested_at' => 'datetime', 'processing_at' => 'datetime', 'succeeded_at' => 'datetime', 'failed_at' => 'datetime', 'unknown_at' => 'datetime'];
    public function admin() { return $this->belongsTo(PlatformAdmin::class, 'platform_admin_id'); }
    public function account() { return $this->belongsTo(PlatformWithdrawalAccount::class, 'platform_withdrawal_account_id'); }
}
