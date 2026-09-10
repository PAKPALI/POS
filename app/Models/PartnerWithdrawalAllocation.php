<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerWithdrawalAllocation extends Model
{
    protected $fillable = ['partner_withdrawal_id', 'partner_commission_id', 'amount'];
    protected $casts = ['amount' => 'integer'];
    public function withdrawal() { return $this->belongsTo(PartnerWithdrawal::class, 'partner_withdrawal_id'); }
    public function commission() { return $this->belongsTo(PartnerCommission::class, 'partner_commission_id'); }
}
