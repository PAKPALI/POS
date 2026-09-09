<?php

namespace App\Console\Commands;

use App\Services\PartnerCommissionService;
use Illuminate\Console\Command;

class MaturePartnerCommissions extends Command
{
    protected $signature = 'partners:mature-commissions {--limit=200 : Nombre maximal de commissions à traiter}';
    protected $description = 'Transfère les commissions partenaires arrivées à maturité vers le solde disponible';

    public function handle(PartnerCommissionService $commissions): int
    {
        $matured = $commissions->matureDue((int) $this->option('limit'));
        $this->info("Commissions rendues disponibles : {$matured}.");

        return self::SUCCESS;
    }
}
