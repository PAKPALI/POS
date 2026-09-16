<?php

namespace App\Models;
use App\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodePromo extends Model
{
    use BelongsToCompany, HasFactory;
    protected $fillable = ['company_id','name','code','normalized_code','created_by','percents','status','expires_at','comments','qr_code'];
    protected $casts = ['expires_at' => 'datetime'];

    public function scopeUsable($query)
    {
        return $query->where('status', 1)
            ->where(fn ($expiry) => $expiry->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public static function normalizeCode(?string $code): string
    {
        return strtoupper(trim((string) $code));
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
