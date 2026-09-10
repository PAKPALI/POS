<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerWithdrawal extends Model
{
    protected $fillable = ['partner_id', 'partner_withdrawal_account_id', 'transaction_id', 'idempotency_key', 'kpp_reference', 'amount', 'fees', 'currency', 'with_fees', 'status', 'provider_status', 'failure_reason', 'requested_at', 'otp_verified_at', 'approved_at', 'processing_at', 'succeeded_at', 'failed_at', 'unknown_at', 'cancelled_at', 'reviewer_id', 'review_reason', 'account_snapshot'];
    protected $casts = ['amount' => 'integer', 'fees' => 'integer', 'with_fees' => 'boolean', 'requested_at' => 'datetime', 'otp_verified_at' => 'datetime', 'approved_at' => 'datetime', 'processing_at' => 'datetime', 'succeeded_at' => 'datetime', 'failed_at' => 'datetime', 'unknown_at' => 'datetime', 'cancelled_at' => 'datetime', 'account_snapshot' => 'array'];
    public function partner() { return $this->belongsTo(Partner::class); }
    public function account() { return $this->belongsTo(PartnerWithdrawalAccount::class, 'partner_withdrawal_account_id'); }
    public function allocations() { return $this->hasMany(PartnerWithdrawalAllocation::class); }
    public function payoutEvents() { return $this->hasMany(PartnerPayoutEvent::class); }
}
