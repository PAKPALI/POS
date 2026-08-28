<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class PlatformAdmin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'is_active', 'must_change_password',
        'last_login_at', 'last_login_ip', 'two_factor_enabled', 'two_factor_code',
        'two_factor_expires_at', 'two_factor_attempts', 'auth_version',
    ];

    protected $hidden = ['password', 'remember_token', 'two_factor_code'];

    protected $casts = [
        'password' => 'hashed',
        'is_active' => 'boolean',
        'must_change_password' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'two_factor_expires_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function auditLogs()
    {
        return $this->hasMany(PlatformAuditLog::class);
    }

    public function hasPlatformPermission(string $permission): bool
    {
        if (!$this->is_active) return false;
        $permissions = config('platform.roles.'.$this->role.'.permissions', []);
        return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
    }

    public function roleLabel(): string
    {
        return (string) config('platform.roles.'.$this->role.'.label', $this->role);
    }
}
