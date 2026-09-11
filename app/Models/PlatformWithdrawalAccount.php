<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformWithdrawalAccount extends Model
{
    protected $fillable = ['platform_admin_id', 'country_code', 'gateway', 'phone_e164', 'phone_fingerprint', 'beneficiary_name', 'status', 'verified_at', 'is_primary'];
    protected $hidden = ['phone_e164', 'phone_fingerprint'];
    protected $casts = ['phone_e164' => 'encrypted', 'verified_at' => 'datetime', 'is_primary' => 'boolean'];

    public function admin() { return $this->belongsTo(PlatformAdmin::class, 'platform_admin_id'); }
    public function withdrawals() { return $this->hasMany(PlatformWithdrawal::class); }
    public function maskedPhone(): string
    {
        $phone = (string) $this->phone_e164;
        return strlen($phone) > 4 ? substr($phone, 0, 4).'••••'.substr($phone, -2) : '••••';
    }
}
