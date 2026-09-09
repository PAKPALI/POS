<?php

namespace App\Models;

use LogicException;
use Illuminate\Database\Eloquent\Model;

class PartnerWalletEntry extends Model
{
    protected $fillable = [
        'partner_id', 'entry_type', 'bucket', 'direction', 'amount', 'currency',
        'source_type', 'source_id', 'idempotency_key', 'occurred_at', 'metadata',
    ];

    protected $casts = [
        'amount' => 'integer',
        'occurred_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Une écriture de portefeuille est immuable.'));
        static::deleting(fn () => throw new LogicException('Une écriture de portefeuille ne peut pas être supprimée.'));
    }

    public function partner() { return $this->belongsTo(Partner::class); }
}
