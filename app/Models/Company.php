<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Company extends Model
{
    use HasFactory;

    protected $table = 'company_settings';

    protected $fillable = [
        'name',
        'slug',
        'public_id',
        'status',
        'timezone',
        'currency',
        'locale',
        'country_code',
        'email',
        'adress',
        'number1',
        'number2',
        'message',
        'logo',
        'description',
        'ecommerce_active',
        'sms_count',
        'whatsapp_count',
        'sale_email_enabled',
        'sale_whatsapp_enabled',
        'sale_sms_enabled',
        'invoice_whatsapp_enabled',
        'invoice_sms_enabled',
        'inventory_email_enabled',
        'inventory_whatsapp_enabled',
        'inventory_sms_enabled',
        'created_by',
    ];

    protected $casts = [
        'ecommerce_active' => 'boolean',
        'sale_email_enabled' => 'boolean',
        'sale_whatsapp_enabled' => 'boolean',
        'sale_sms_enabled' => 'boolean',
        'invoice_whatsapp_enabled' => 'boolean',
        'invoice_sms_enabled' => 'boolean',
        'inventory_email_enabled' => 'boolean',
        'inventory_whatsapp_enabled' => 'boolean',
        'inventory_sms_enabled' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Company $company) {
            if (empty($company->public_id)) {
                $company->public_id = (string) Str::uuid();
            }

            $company->slug = static::generateUniqueSlug($company->slug ?: $company->name);
        });
    }

    public static function generateUniqueSlug(string $value): string
    {
        $base = Str::limit(Str::slug($value), 220, '');
        $base = $base !== '' ? $base : 'entreprise';
        $slug = $base;
        $suffix = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    // ─── Relations ───────────────────────────────────────

    public function users()
    {
        return $this->belongsToMany(User::class, 'company_user', 'company_id', 'user_id')
            ->withPivot(['role_id', 'status', 'invited_by', 'joined_at', 'last_accessed_at'])
            ->withTimestamps();
    }

    public function memberships()
    {
        return $this->hasMany(CompanyUser::class);
    }

    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public function invitations()
    {
        return $this->hasMany(CompanyInvitation::class);
    }

    public function managers()
    {
        return $this->hasMany(EcommerceManager::class, 'company_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'company_id');
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
}
