<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        'country_code',
        'email',
        'password',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // ─── Multi-Tenant Relations ──────────────────────────

    /**
     * Companies this user belongs to (via company_user pivot).
     */
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_user', 'user_id', 'company_id')
            ->withPivot(['role_id', 'status', 'invited_by', 'joined_at', 'last_accessed_at'])
            ->withTimestamps();
    }

    /**
     * Company memberships (CompanyUser records).
     */
    public function memberships()
    {
        return $this->hasMany(CompanyUser::class);
    }

    /**
     * Active memberships only.
     */
    public function activeMemberships()
    {
        return $this->hasMany(CompanyUser::class)->where('status', 'active');
    }

    public function notificationPreferences()
    {
        return $this->hasMany(NotificationRecipient::class);
    }

    /**
     * Companies where user is active.
     */
    public function activeCompanies()
    {
        return $this->belongsToMany(Company::class, 'company_user', 'user_id', 'company_id')
            ->wherePivot('status', 'active')
            ->withPivot(['role_id', 'joined_at'])
            ->withTimestamps();
    }

    // ─── E-commerce (legacy — to be removed in Phase 3) ─

    public function ecommerceCompanies()
    {
        return $this->belongsToMany(Company::class, 'ecommerce_managers', 'user_id', 'company_id');
    }

    // ─── Helpers ─────────────────────────────────────────

    /**
     * Check if user belongs to a specific company.
     */
    public function belongsToCompany(int $companyId): bool
    {
        return $this->companies()->where('company_settings.id', $companyId)->exists();
    }

    /**
     * Check if user is active member of a specific company.
     */
    public function isMemberOf(int $companyId): bool
    {
        return $this->activeCompanies()->where('company_settings.id', $companyId)->exists();
    }

    /**
     * Get the user's membership for a specific company.
     */
    public function getMembershipFor(int $companyId): ?CompanyUser
    {
        return $this->memberships()
            ->where('company_id', $companyId)
            ->first();
    }

    /**
     * Check if user has a specific permission in a company context.
     */
    public function hasPermissionIn(int $companyId, string $permissionKey): bool
    {
        $membership = $this->getMembershipFor($companyId);

        if (!$membership || !$membership->isActive()) {
            return false;
        }

        return $membership->hasPermission($permissionKey);
    }

    /**
     * Check if user is owner/admin of a specific company.
     */
    public function isOwnerOf(int $companyId): bool
    {
        $membership = $this->getMembershipFor($companyId);

        return $membership
            && $membership->role
            && in_array($membership->role->key, ['owner', 'admin']);
    }
}
