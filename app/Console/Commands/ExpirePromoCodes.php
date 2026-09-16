<?php

namespace App\Console\Commands;

use App\Models\CodePromo;
use Illuminate\Console\Command;

class ExpirePromoCodes extends Command
{
    protected $signature = 'promo-codes:expire';
    protected $description = 'Désactive les codes promotionnels arrivés à expiration';

    public function handle(): int
    {
        $count = CodePromo::withoutCompanyScope()
            ->where('status', 1)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['status' => 0, 'updated_at' => now()]);

        $this->info("{$count} code(s) promotionnel(s) expiré(s).");

        return self::SUCCESS;
    }
}
