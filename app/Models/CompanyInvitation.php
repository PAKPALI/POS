<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CompanyInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'email',
        'role_id',
        'invited_by',
        'token_hash',
        'expires_at',
        'last_sent_at',
        'accepted_at',
        'declined_at',
        'revoked_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
        'revoked_at' => 'datetime',
        'last_sent_at' => 'datetime',
    ];

    // ─── Relations ───────────────────────────────────────

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    // ─── Helpers ─────────────────────────────────────────

    public static function generateToken(): string
    {
        return Str::random(64);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    public function isPending(): bool
    {
        return !$this->accepted_at && !$this->declined_at && !$this->revoked_at && !$this->isExpired();
    }

    public function getStatusLabelAttribute(): string
    {
        return match (true) {
            (bool) $this->accepted_at => 'Acceptée',
            (bool) $this->declined_at => 'Refusée',
            (bool) $this->revoked_at => 'Révoquée',
            $this->isExpired() => 'Expirée',
            default => 'En attente',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match (true) {
            (bool) $this->accepted_at => 'success',
            (bool) $this->declined_at => 'danger',
            (bool) $this->revoked_at => 'dark',
            $this->isExpired() => 'secondary',
            default => 'warning',
        };
    }
}
