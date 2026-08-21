<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invitation — {{ $invitation->company->name }}</title>
    <link href="{{ asset('hub/assets/css/vendor.min.css') }}" rel="stylesheet">
    <link href="{{ asset('hub/assets/css/app.min.css') }}" rel="stylesheet">
    <style>
        :root {
            --invite-accent: #22c55e;
            --invite-surface: #121a24;
            --invite-panel: #0c131c;
            --invite-border: #405168;
            --invite-muted: #a8b5c5;
        }

        body.invitation-page {
            min-height: 100vh;
            color: #f8fafc;
            background:
                radial-gradient(circle at 12% 10%, rgba(34, 197, 94, .16), transparent 28rem),
                radial-gradient(circle at 90% 88%, rgba(59, 130, 246, .12), transparent 30rem),
                #070b11;
        }

        .invitation-page::before {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            opacity: .24;
            background-image: linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        .invitation-container {
            position: relative;
            z-index: 1;
            width: min(100% - 28px, 720px);
            margin: 0 auto;
            padding: 48px 0;
        }

        .invitation-card {
            overflow: hidden;
            border: 1px solid rgba(74, 222, 128, .42);
            border-radius: 22px;
            background: rgba(18, 26, 36, .97);
            box-shadow: 0 28px 80px rgba(0, 0, 0, .5), 0 0 0 1px rgba(255, 255, 255, .025) inset;
        }

        .invitation-header {
            padding: 34px 34px 26px;
            text-align: center;
            border-bottom: 1px solid rgba(148, 163, 184, .2);
            background: linear-gradient(180deg, rgba(34, 197, 94, .09), transparent);
        }

        .company-logo,
        .company-initial {
            width: 76px;
            height: 76px;
            margin: 0 auto 18px;
            border: 3px solid rgba(74, 222, 128, .55);
            border-radius: 20px;
            background: #f8fafc;
            box-shadow: 0 12px 30px rgba(0, 0, 0, .28);
        }

        .company-logo { object-fit: contain; padding: 7px; }
        .company-initial {
            display: grid;
            place-items: center;
            color: #14532d;
            font-size: 2rem;
            font-weight: 800;
        }

        .invitation-eyebrow {
            margin-bottom: 8px;
            color: #86efac;
            font-size: .76rem;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .invitation-title {
            margin: 0;
            color: #fff;
            font-size: clamp(1.55rem, 5vw, 2rem);
            font-weight: 750;
            line-height: 1.2;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-top: 16px;
            padding: 8px 13px;
            border: 1px solid rgba(134, 239, 172, .34);
            border-radius: 999px;
            color: #dcfce7;
            background: rgba(34, 197, 94, .11);
            font-size: .9rem;
        }

        .invitation-body { padding: 30px 34px 34px; }
        .invitation-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 22px;
        }

        .meta-item {
            min-width: 0;
            padding: 14px 15px;
            border: 1px solid #344358;
            border-radius: 13px;
            background: #0b121b;
        }

        .meta-label {
            display: block;
            margin-bottom: 4px;
            color: var(--invite-muted);
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .meta-value {
            display: block;
            overflow-wrap: anywhere;
            color: #f8fafc;
            font-weight: 650;
        }

        .account-panel {
            padding: 22px;
            border: 1px solid var(--invite-border);
            border-radius: 16px;
            background: var(--invite-panel);
            box-shadow: 0 1px 0 rgba(255,255,255,.035) inset;
        }

        .account-panel-title {
            margin-bottom: 5px;
            color: #fff;
            font-size: 1.05rem;
            font-weight: 750;
        }

        .account-panel-copy {
            margin-bottom: 20px;
            color: #c4ceda;
            line-height: 1.55;
        }

        .invitation-page .form-label {
            margin-bottom: 7px;
            color: #edf4fb;
            font-size: .9rem;
            font-weight: 700;
        }

        .invitation-page .form-control {
            min-height: 48px;
            border: 2px solid #94a3b8;
            border-radius: 11px;
            color: #0f172a;
            background: #f8fafc;
            box-shadow: none;
            transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
        }

        .invitation-page .form-control:hover { border-color: #cbd5e1; }
        .invitation-page .form-control:focus {
            border-color: var(--invite-accent);
            color: #0f172a;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(34, 197, 94, .2);
        }

        .field-help {
            margin-top: 7px;
            color: #9eacbd;
            font-size: .79rem;
        }

        .invitation-page .alert {
            border-width: 1px;
            border-radius: 13px;
            line-height: 1.5;
        }

        .invitation-page .alert-info {
            border-color: rgba(56, 189, 248, .38);
            color: #d9f4ff;
            background: rgba(14, 116, 144, .2);
        }

        .invitation-page .alert-warning {
            border-color: rgba(251, 191, 36, .4);
            color: #fef3c7;
            background: rgba(146, 64, 14, .22);
        }

        .invitation-page .alert-danger {
            border-color: rgba(248, 113, 113, .4);
            color: #fee2e2;
            background: rgba(153, 27, 27, .24);
        }

        .accept-button,
        .decline-button {
            min-height: 49px;
            border-radius: 11px;
            font-weight: 750;
        }

        .accept-button {
            border-color: var(--invite-accent);
            color: #052e16;
            background: var(--invite-accent);
            box-shadow: 0 10px 24px rgba(34, 197, 94, .2);
        }

        .accept-button:hover,
        .accept-button:focus {
            border-color: #4ade80;
            color: #052e16;
            background: #4ade80;
        }

        .decline-button {
            border-color: #ef4444;
            color: #fecaca;
            background: rgba(127, 29, 29, .12);
        }

        .decline-button:hover,
        .decline-button:focus {
            border-color: #f87171;
            color: #fff;
            background: rgba(127, 29, 29, .3);
        }

        .security-note {
            margin: 18px 0 0;
            color: #8f9cad;
            font-size: .78rem;
            line-height: 1.45;
            text-align: center;
        }

        @media (max-width: 575.98px) {
            body.invitation-page {
                height: 100vh;
                height: 100dvh;
                min-height: 0;
                overflow: hidden;
            }

            .invitation-container {
                display: flex;
                height: 100%;
                padding: 12px 0;
            }

            .invitation-card {
                display: flex;
                flex: 1;
                flex-direction: column;
                min-height: 0;
                border-radius: 17px;
            }

            .invitation-header {
                flex: 0 0 auto;
                padding: 22px 20px 18px;
            }

            .company-logo,
            .company-initial {
                width: 60px;
                height: 60px;
                margin-bottom: 13px;
                border-radius: 16px;
            }

            .company-initial { font-size: 1.6rem; }
            .role-badge { margin-top: 12px; }

            .invitation-body {
                flex: 1 1 auto;
                min-height: 0;
                overflow-x: hidden;
                overflow-y: auto;
                overscroll-behavior: contain;
                padding: 22px 18px 24px;
                scrollbar-color: #4ade80 #0b121b;
                scrollbar-width: thin;
                -webkit-overflow-scrolling: touch;
            }

            .invitation-body::-webkit-scrollbar { width: 6px; }
            .invitation-body::-webkit-scrollbar-track { background: #0b121b; }
            .invitation-body::-webkit-scrollbar-thumb {
                border-radius: 999px;
                background: #4ade80;
            }

            .invitation-meta { grid-template-columns: 1fr; }
            .account-panel { padding: 18px 15px; }
        }

        @media (max-width: 575.98px) and (max-height: 700px) {
            .invitation-header { padding: 15px 18px 13px; }
            .company-logo,
            .company-initial {
                width: 48px;
                height: 48px;
                margin-bottom: 9px;
                border-radius: 13px;
            }
            .invitation-eyebrow { margin-bottom: 4px; }
            .invitation-title { font-size: 1.3rem; }
            .role-badge { margin-top: 8px; padding: 6px 10px; }
        }
    </style>
</head>
<body class="invitation-page">
<main class="invitation-container">
    <section class="invitation-card" aria-labelledby="invitation-title">
        <header class="invitation-header">
            @if($invitation->company->logo)
                <img src="{{ asset($invitation->company->logo) }}" alt="Logo de {{ $invitation->company->name }}" class="company-logo">
            @else
                <div class="company-initial" aria-hidden="true">{{ mb_strtoupper(mb_substr($invitation->company->name, 0, 1)) }}</div>
            @endif
            <div class="invitation-eyebrow">Invitation professionnelle</div>
            <h1 class="invitation-title" id="invitation-title">Rejoindre {{ $invitation->company->name }}</h1>
            <div class="role-badge"><span aria-hidden="true">✓</span> Rôle proposé : <strong>{{ $invitation->role?->name ?? 'Non disponible' }}</strong></div>
        </header>

        <div class="invitation-body">
            @if(!$invitation->isPending())
                <div class="alert alert-warning mb-0">Cette invitation est {{ mb_strtolower($invitation->status_label) }} et ne peut plus être utilisée.</div>
            @else
                <div class="invitation-meta" aria-label="Informations de l’invitation">
                    <div class="meta-item">
                        <span class="meta-label">Compte invité</span>
                        <span class="meta-value">{{ $invitation->email }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Valable jusqu’au</span>
                        <span class="meta-value">{{ $invitation->expires_at->format('d/m/Y à H:i') }}</span>
                    </div>
                </div>

                @if(auth()->check() && strcasecmp(auth()->user()->email, $invitation->email) !== 0)
                    <div class="alert alert-warning">
                        Vous êtes connecté avec <strong>{{ auth()->user()->email }}</strong>. En continuant, l’application ouvrira le compte <strong>{{ $invitation->email }}</strong> pour rejoindre {{ $invitation->company->name }}.
                    </div>
                @endif

                <form method="POST" action="{{ route('invitations.accept', $token) }}" class="account-panel">
                    @csrf
                    @if($existingUser)
                        <h2 class="account-panel-title">Votre compte est prêt</h2>
                        <p class="account-panel-copy mb-4">Aucun mot de passe temporaire n’est nécessaire. Ce lien personnel confirme votre invitation sans modifier votre mot de passe actuel.</p>
                    @else
                        <h2 class="account-panel-title">Créez votre accès</h2>
                        <p class="account-panel-copy">Ces informations vous permettront de vous reconnecter ensuite à toutes les entreprises auxquelles vous avez accès.</p>

                        <div class="mb-3">
                            <label class="form-label" for="invitation-name">Nom complet</label>
                            <input id="invitation-name" name="name" value="{{ old('name') }}" class="form-control" autocomplete="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="invitation-phone">Téléphone <span class="fw-normal text-white-50">(facultatif)</span></label>
                            <input id="invitation-phone" type="tel" name="phone" value="{{ old('phone') }}" class="form-control" autocomplete="tel">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="invitation-password">Mot de passe</label>
                            <input id="invitation-password" type="password" name="password" class="form-control" autocomplete="new-password" required minlength="8">
                            <div class="field-help">Au moins 8 caractères.</div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="invitation-password-confirmation">Confirmer le mot de passe</label>
                            <input id="invitation-password-confirmation" type="password" name="password_confirmation" class="form-control" autocomplete="new-password" required minlength="8">
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
                    @endif

                    <button class="btn accept-button w-100" type="submit" data-loading-text="Validation en cours…">
                        {{ $existingUser ? 'Accepter et ouvrir mon compte' : 'Créer mon compte et rejoindre l’entreprise' }}
                    </button>
                </form>

                <form method="POST" action="{{ route('invitations.decline', $token) }}" class="mt-3" onsubmit="return confirm('Refuser définitivement cette invitation ?')">
                    @csrf
                    <button class="btn decline-button w-100" type="submit" data-loading-text="Refus en cours…">Refuser l’invitation</button>
                </form>

                <p class="security-note">Lien sécurisé, personnel et utilisable une seule fois. Ne le transférez à personne.</p>
            @endif
        </div>
    </section>
</main>
<script src="{{ asset('hub/assets/js/server-button-loader.js') }}"></script>
</body>
</html>
