<?php

namespace App\Console\Commands;

use App\Models\Partner;
use App\Models\PartnerWalletEntry;
use App\Models\PartnerWithdrawalAccount;
use Illuminate\Console\Command;

class SeedPartnerWithdrawalPreview extends Command
{
    protected $signature = 'partners:seed-withdrawal-preview {partner : Identifiant du partenaire local}';
    protected $description = 'Prépare localement des données fictives pour visualiser le formulaire de retrait, sans transfert ni confirmation';

    public function handle(): int
    {
        if (!app()->environment(['local', 'testing'])) {
            $this->error('Cette commande est exclusivement disponible en environnement local ou de test.');
            return self::FAILURE;
        }

        $partner = Partner::query()->findOrFail((int) $this->argument('partner'));
        $partner->update(['qualified_clients_count' => max(1, (int) $partner->qualified_clients_count)]);
        PartnerWalletEntry::firstOrCreate(['idempotency_key' => 'withdrawal-preview:'.$partner->id.':credit'], [
            'partner_id' => $partner->id,
            'entry_type' => 'preview_credit',
            'bucket' => 'available',
            'direction' => 'credit',
            'amount' => 10000,
            'currency' => 'XOF',
            'source_type' => 'withdrawal_preview',
            'source_id' => $partner->id,
            'occurred_at' => now(),
            'metadata' => ['local_preview' => true],
        ]);
        PartnerWithdrawalAccount::firstOrCreate(['partner_id' => $partner->id, 'phone_fingerprint' => hash('sha256', '+22896000001')], [
            'country_code' => 'TG',
            'gateway' => 'MOOV-MONEY-TG',
            'phone_e164' => '+22896000001',
            'beneficiary_name' => 'Compte Démonstration',
            'status' => 'verified',
            'verified_at' => now(),
            'is_primary' => false,
        ]);

        $this->info('Aperçu local prêt : 10 000 XOF fictifs, compte de démonstration vérifié, aucun retrait créé.');
        return self::SUCCESS;
    }
}
