<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyUser extends Model
{
    use HasFactory;

    protected $table = 'company_user';

    protected $fillable = [
        'company_id',
        'user_id',
        'role_id',
        'status',
        'invited_by',
        'joined_at',
        'last_accessed_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'last_accessed_at' => 'datetime',
    ];

    // ─── Relations ───────────────────────────────────────

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    // ─── Scopes ──────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // ─── Helpers ─────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasPermission(string $permissionKey): bool
    {
        if (!$this->role) {
            return false;
        }

        if ($this->role->key === 'owner') {
            return true;
        }

        return $this->role->permissions->contains('key', $permissionKey);
    }
}
