<?php

namespace App\Jobs;

use App\Mail\PartnerPlatformAlertMail;
use App\Models\Partner;
use App\Models\PlatformAdmin;
use App\Services\PlatformConfigurationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPartnerPlatformAlert implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $uniqueFor = 3600;

    public function __construct(
        public string $event,
        public int $partnerId,
        public string $eventKey,
        public array $context = [],
    ) {}

    public function uniqueId(): string
    {
        return 'partner-platform-alert:'.$this->event.':'.$this->eventKey;
    }

    public function handle(PlatformConfigurationService $configuration): void
    {
        $definition = self::definitions()[$this->event] ?? null;
        if (!$definition
            || !$configuration->boolean('services.email.enabled', true)
            || !$configuration->boolean('partners.alerts.enabled', true)
            || !$configuration->boolean($definition['setting'], false)) {
            return;
        }

        $partner = Partner::query()->find($this->partnerId);
        if (!$partner) return;

        $admins = $this->recipients($configuration, $definition['audience']);
        if ($admins->isEmpty()) return;

        $details = array_merge([
            'Partenaire' => $partner->name,
            'Identifiant' => '@'.$partner->username,
            'E-mail' => $partner->email,
            'Pays' => $partner->country_code,
        ], $this->contextDetails());

        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new PartnerPlatformAlertMail(
                title: $definition['title'],
                intro: $definition['intro'],
                partner: $partner,
                details: $details,
                actionUrl: route('platform.partners.show', $partner),
            ));
        }
    }

    private function recipients(PlatformConfigurationService $configuration, string $audience)
    {
        $stored = $configuration->get('partners.alerts.recipient_admin_ids', '[]');
        $ids = is_array($stored) ? $stored : (json_decode((string) $stored, true) ?: []);
        $query = PlatformAdmin::query()->where('is_active', true)->whereNotNull('email')->where('email', '<>', '');

        if ($ids !== []) {
            return $query->whereIn('id', array_map('intval', $ids))->get();
        }

        $roles = $audience === 'finance' ? ['super_admin', 'finance'] : ['super_admin', 'support'];
        return $query->whereIn('role', $roles)->get();
    }

    private function contextDetails(): array
    {
        $context = $this->context;
        $details = [];
        foreach ([
            'old_code' => 'Ancien code',
            'new_code' => 'Nouveau code',
            'type' => 'Type',
            'plan' => 'Plan',
            'gross_amount' => 'Brut',
            'discount_amount' => 'Remise',
            'net_paid_amount' => 'Net encaissé',
            'commission_rate' => 'Taux',
            'commission_amount' => 'Commission',
            'withdrawal_id' => 'Retrait',
            'amount' => 'Montant demandé',
            'fees' => 'Frais estimés',
            'status' => 'Statut',
            'reason' => 'Motif',
        ] as $key => $label) {
            if (!array_key_exists($key, $context) || $context[$key] === null || $context[$key] === '') continue;
            $value = $context[$key];
            if (in_array($key, ['gross_amount', 'discount_amount', 'net_paid_amount', 'commission_amount', 'amount', 'fees'], true)) {
                $value = number_format((int) $value, 0, ',', ' ').' XOF';
            }
            if ($key === 'commission_rate') $value = number_format((float) $value / 100, 2, ',', ' ').' %';
            $details[$label] = (string) $value;
        }
        return $details;
    }

    private static function definitions(): array
    {
        return [
            'partner_registered' => ['setting' => 'partners.alerts.partner_registered', 'title' => 'Nouveau partenaire inscrit', 'intro' => 'Un nouveau compte partenaire vient d’être créé et attend la vérification de son adresse e-mail.', 'audience' => 'support'],
            'email_verified' => ['setting' => 'partners.alerts.email_verified', 'title' => 'Partenaire activé', 'intro' => 'Un partenaire vient de confirmer son adresse e-mail et son compte est maintenant actif.', 'audience' => 'support'],
            'code_changed' => ['setting' => 'partners.alerts.code_changed', 'title' => 'Code partenaire modifié', 'intro' => 'Un partenaire vient de modifier son code promotionnel principal.', 'audience' => 'support'],
            'commission_created' => ['setting' => 'partners.alerts.commission_created', 'title' => 'Nouvelle commission partenaire', 'intro' => 'Une commission vient d’être enregistrée après la confirmation d’un paiement d’abonnement.', 'audience' => 'finance'],
            'withdrawal_requested' => ['setting' => 'partners.alerts.withdrawal_requested', 'title' => 'Demande de retrait partenaire', 'intro' => 'Un partenaire vient de confirmer une demande de retrait Mobile Money.', 'audience' => 'finance'],
            'withdrawal_succeeded' => ['setting' => 'partners.alerts.withdrawal_succeeded', 'title' => 'Retrait partenaire réussi', 'intro' => 'KPrimePay a confirmé le versement d’un retrait partenaire.', 'audience' => 'finance'],
            'withdrawal_failed' => ['setting' => 'partners.alerts.withdrawal_failed', 'title' => 'Retrait partenaire échoué', 'intro' => 'Un retrait partenaire a été refusé ou libéré après un échec confirmé.', 'audience' => 'finance'],
            'withdrawal_unknown' => ['setting' => 'partners.alerts.withdrawal_unknown', 'title' => 'Retrait partenaire à réconcilier', 'intro' => 'La réponse du prestataire est incertaine : le montant reste réservé et nécessite une vérification.', 'audience' => 'finance'],
        ];
    }
}
