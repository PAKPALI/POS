@extends('layouts.partner')

@section('title', 'Mon code partenaire')
@section('page-title', 'Mon code')

@section('content')
    <x-ui.page-header
        title="Mon code partenaire"
        description="Partagez ce code pour faire connaître Maxanou à vos futurs abonnés."
        eyebrow="Acquisition partenaire"
        icon="bi-ticket-perforated"
    >
        <x-slot:actions>
            <x-ui.badge variant="success"><i class="bi bi-check-circle me-1" aria-hidden="true"></i>Code actif</x-ui.badge>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="partner-code-layout">
        <section class="saas-panel partner-code-card" aria-labelledby="partner-code-title">
            <div class="partner-code-card-heading">
                <div>
                    <span class="saas-ui-eyebrow">Votre identifiant de partage</span>
                    <h2 id="partner-code-title">Un code simple à transmettre</h2>
                    <p>Vos filleuls pourront le renseigner lors de leur première souscription éligible.</p>
                </div>
                <span class="partner-code-card-icon" aria-hidden="true"><i class="bi bi-share"></i></span>
            </div>

            <div class="partner-code-display">
                <span>Code actuellement actif</span>
                <code id="partnerCodeValue">{{ $code->code }}</code>
                <x-ui.button id="partnerCopyCode" type="button" variant="secondary">
                    <i class="bi bi-copy" aria-hidden="true"></i><span>Copier</span>
                </x-ui.button>
            </div>

            <div class="saas-notice saas-notice-info">
                <i class="bi bi-info-circle" aria-hidden="true"></i>
                <span>Ce code ouvre droit à une remise de <strong>{{ $discountPercent }} %</strong> selon les conditions de la première souscription éligible.</span>
            </div>
        </section>

        <section class="saas-panel partner-code-card" aria-labelledby="partner-code-customize-title">
            <div class="partner-code-card-heading">
                <div>
                    <span class="saas-ui-eyebrow">Personnalisation</span>
                    <h2 id="partner-code-customize-title">Choisir un autre code</h2>
                    <p>Utilisez 4 à 24 lettres ou chiffres, sans espace ni caractère spécial.</p>
                </div>
                <span class="partner-code-card-icon" aria-hidden="true"><i class="bi bi-pencil-square"></i></span>
            </div>

            <form method="POST" action="{{ route('partner.code.update') }}" class="partner-code-form">
                @csrf
                @method('PUT')
                <x-ui.input
                    id="code"
                    name="code"
                    label="Votre code partenaire"
                    :value="old('code', $code->code)"
                    hint="Les anciens codes sont désactivés et ne sont pas réattribués."
                    error="{{ $errors->first('code') }}"
                    maxlength="24"
                    pattern="[A-Za-z0-9]{4,24}"
                    autocomplete="off"
                    required
                />
                <p id="partnerCodeAvailability" class="partner-code-availability" role="status" aria-live="polite"></p>
                <p class="partner-code-form-note"><i class="bi bi-shield-check" aria-hidden="true"></i><span>Après l’enregistrement, le délai avant un nouveau changement est de 30 jours par défaut (il peut être ajusté par l’administration). Désactiver un ancien code n’annule pas les paiements déjà liés à ce code ni les conditions acquises par les clients concernés.</span></p>
                <div class="partner-code-form-actions">
                    <x-ui.button id="partnerCodeSubmit" type="submit" variant="primary" loading-text="Enregistrement…">Enregistrer le code</x-ui.button>
                </div>
            </form>
        </section>
    </div>

    <section class="saas-panel partner-code-history" aria-labelledby="partner-code-history-title">
        <div class="partner-code-card-heading">
            <div>
                <span class="saas-ui-eyebrow">Traçabilité</span>
                <h2 id="partner-code-history-title">Historique des changements</h2>
                <p>Retrouvez les derniers codes remplacés et le nouveau code activé à chaque changement.</p>
            </div>
            <span class="partner-code-card-icon" aria-hidden="true"><i class="bi bi-clock-history"></i></span>
        </div>

        @if($history->isEmpty())
            <div class="partner-code-history-empty"><i class="bi bi-clock-history" aria-hidden="true"></i><span>Aucun changement de code enregistré pour le moment.</span></div>
        @else
            <div class="partner-code-history-list">
                @foreach($history as $change)
                    <article class="partner-code-history-item">
                        <div class="partner-code-history-date"><i class="bi bi-calendar3" aria-hidden="true"></i><span>{{ $change->created_at?->format('d/m/Y à H:i') }}</span></div>
                        <div class="partner-code-history-codes">
                            <div><span>Ancien code</span><code>{{ data_get($change->old_values, 'code', '—') }}</code></div>
                            <i class="bi bi-arrow-right partner-code-history-arrow" aria-hidden="true"></i>
                            <div><span>Nouveau code</span><code>{{ data_get($change->new_values, 'code', '—') }}</code></div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const button = document.getElementById('partnerCopyCode');
            const value = document.getElementById('partnerCodeValue')?.textContent.trim();
            if (button && value) {
                button.addEventListener('click', async () => {
                    try {
                        await navigator.clipboard.writeText(value);
                        button.querySelector('span').textContent = 'Copié';
                        button.querySelector('i').className = 'bi bi-check2';
                        window.setTimeout(() => {
                            button.querySelector('span').textContent = 'Copier';
                            button.querySelector('i').className = 'bi bi-copy';
                        }, 1800);
                    } catch (error) {
                        button.querySelector('span').textContent = 'Copie impossible';
                    }
                });
            }

            const form = document.querySelector('.partner-code-form');
            const input = document.getElementById('code');
            const submit = document.getElementById('partnerCodeSubmit');
            const feedback = document.getElementById('partnerCodeAvailability');
            if (!form || !input || !submit || !feedback) return;

            const availabilityUrl = @json(route('partner.code.availability'));
            let timer = null;
            let controller = null;
            let requestId = 0;
            let availabilityConfirmed = false;

            const setAvailability = (state, message, available = false) => {
                feedback.className = `partner-code-availability is-${state}`;
                feedback.innerHTML = `<i class="bi ${available ? 'bi-check-circle' : state === 'checking' ? 'bi-arrow-repeat' : 'bi-exclamation-circle'}" aria-hidden="true"></i><span>${message}</span>`;
                availabilityConfirmed = available;
                submit.disabled = !available;
                submit.setAttribute('aria-disabled', String(!available));
            };

            const checkAvailability = async () => {
                const currentRequest = ++requestId;
                const code = input.value.trim();
                if (!code) {
                    setAvailability('empty', 'Saisissez un code partenaire.');
                    return;
                }

                if (controller) controller.abort();
                controller = new AbortController();
                setAvailability('checking', 'Vérification de la disponibilité…');
                try {
                    const response = await fetch(`${availabilityUrl}?code=${encodeURIComponent(code)}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                        signal: controller.signal,
                    });
                    if (!response.ok) throw new Error('availability');
                    const data = await response.json();
                    if (currentRequest !== requestId) return;
                    setAvailability(data.state || (data.available ? 'available' : 'taken'), data.message || 'La disponibilité n’a pas pu être déterminée.', Boolean(data.available));
                } catch (error) {
                    if (error.name === 'AbortError' || currentRequest !== requestId) return;
                    setAvailability('error', 'La vérification est momentanément indisponible. Réessayez.');
                }
            };

            input.addEventListener('input', () => {
                window.clearTimeout(timer);
                timer = window.setTimeout(checkAvailability, 280);
            });
            form.addEventListener('submit', (event) => {
                if (!availabilityConfirmed) {
                    event.preventDefault();
                    input.focus();
                }
            });
            checkAvailability();
        });
    </script>
@endpush
