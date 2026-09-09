<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\PartnerResetPasswordNotification;

class Partner extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'username', 'normalized_username', 'email', 'normalized_email', 'email_verified_at',
        'phone_country_code', 'phone_e164', 'country_code', 'password', 'status',
        'two_factor_login_enabled', 'auth_version', 'appearance_mode', 'accent_color',
        'qualified_clients_count', 'current_rate_bps', 'last_login_at', 'last_login_ip',
        'suspended_at', 'suspension_reason',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'password' => 'hashed',
        'email_verified_at' => 'datetime',
        'two_factor_login_enabled' => 'boolean',
        'last_login_at' => 'datetime',
        'suspended_at' => 'datetime',
    ];

    public function getEmailForVerification(): string
    {
        return (string) $this->email;
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new PartnerResetPasswordNotification($token));
    }

    public function promoCodes()
    {
        return $this->hasMany(PartnerPromoCode::class);
    }

    public function twoFactorChallenges()
    {
        return $this->hasMany(PartnerTwoFactorChallenge::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(PartnerAuditLog::class);
    }

    public function attributions()
    {
        return $this->hasMany(PartnerAttribution::class);
    }

    public function commissions()
    {
        return $this->hasMany(PartnerCommission::class);
    }

    public function walletEntries()
    {
        return $this->hasMany(PartnerWalletEntry::class);
    }
}
