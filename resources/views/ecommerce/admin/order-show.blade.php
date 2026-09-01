@extends('layouts.saas')

@push('styles')
    <link href="{{ asset('hub/assets/css/saas-pages.css') }}?v=20260901-15" rel="stylesheet">
@endpush

@section('content')
    <div class="saas-page-heading">
        <div>
            <h1>Commande #{{ $order->code }}</h1>
            <p>Détail de la commande, produits commandés et actions de traitement.</p>
        </div>
        <a href="{{ route('ecommerce.orders.index') }}" class="saas-btn saas-btn-ghost">
            <i class="bi bi-arrow-left" aria-hidden="true"></i> Retour aux commandes
        </a>
    </div>

    @php
        $statusLabels = ['pending' => 'En attente', 'confirmed' => 'Confirmée', 'converted' => 'Passée en vente', 'cancelled' => 'Annulée'];
        $statusClasses = ['pending' => 'is-inactive', 'confirmed' => 'is-active', 'converted' => 'is-active', 'cancelled' => 'is-inactive'];
    @endphp

    <div class="row g-4">
        <div class="col-lg-6">
            {{-- Informations client --}}
            <div class="saas-card mb-4">
                <div class="saas-card-head">
                    <div>
                        <h2>Informations client</h2>
                        <p class="saas-card-description">Coordonnées et livraison.</p>
                    </div>
                </div>
                <div class="saas-detail-list">
                    <div>
                        <dt>Nom</dt>
                        <dd>{{ $order->customer_name }}</dd>
                    </div>
                    <div>
                        <dt>Téléphone</dt>
                        <dd>{{ $order->customer_phone }}</dd>
                    </div>
                    <div>
                        <dt>E-mail</dt>
                        <dd>{{ $order->customer_email ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt>Adresse</dt>
                        <dd>{{ $order->customer_address ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt>Localisation</dt>
                        <dd>
                            @if($order->delivery_location_url)
                                <a href="{{ $order->delivery_location_url }}" class="saas-btn saas-btn-outline saas-btn-sm" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-geo-alt me-1"></i> Google Maps
                                </a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    @if($order->notes)
                    <div>
                        <dt>Notes</dt>
                        <dd>{{ $order->notes }}</dd>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Traitement --}}
            <div class="saas-card">
                <div class="saas-card-head">
                    <div>
                        <h2>Traitement</h2>
                        <p class="saas-card-description">Statut et actions sur cette commande.</p>
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <span class="saas-status-badge {{ $statusClasses[$order->status] ?? 'is-inactive' }}">{{ $statusLabels[$order->status] ?? ucfirst($order->status) }}</span>
                </div>

                @if($order->status === 'converted' && $order->sale)
                    <div class="saas-card" style="background: rgba(32, 191, 169, .06); border-color: rgba(32, 191, 169, .2);">
                        <p style="margin: 0; color: var(--ds-success, #35C98B); font-weight: 600; font-size: .88rem;">
                            <i class="bi bi-check-circle me-1"></i>
                            Vente <strong>#{{ $order->sale->code }}</strong> créée le {{ $order->converted_at?->format('d/m/Y à H:i') }}
                            par {{ $order->convertedBy?->name ?? 'un utilisateur supprimé' }}.
                        </p>
                    </div>
                @elseif($order->status === 'cancelled')
                    <div class="saas-card" style="background: rgba(255, 98, 110, .06); border-color: rgba(255, 98, 110, .2);">
                        <p style="margin: 0 0 6px; color: var(--ds-danger, #FF626E); font-weight: 600; font-size: .88rem;">
                            <i class="bi bi-x-circle me-1"></i>
                            Annulée le {{ $order->cancelled_at?->format('d/m/Y à H:i') }} par {{ $order->cancelledBy?->name ?? 'un utilisateur supprimé' }}.
                        </p>
                        <p style="margin: 0; color: var(--ds-text-secondary); font-size: .82rem;"><strong>Motif :</strong> {{ $order->cancellation_reason }}</p>
                    </div>
                @elseif(in_array($order->status, ['pending', 'confirmed'], true))
                    <p style="color: var(--ds-text-muted); font-size: .82rem; margin-bottom: 16px;">La commande ne modifie pas le stock. Passez-la en vente pour diminuer le stock et alimenter les caisses.</p>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        @if($canConvertToSale)
                            <button type="button" id="executeOrder" class="saas-btn saas-btn-primary" data-no-server-loader>
                                <i class="bi bi-cash-stack me-1"></i> Passer en vente
                            </button>
                        @endif
                        <button type="button" id="cancelOrder" class="saas-btn saas-btn-danger" data-no-server-loader>
                            <i class="bi bi-x-lg me-1"></i> Annuler
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-6">
            {{-- Produits --}}
            <div class="saas-card">
                <div class="saas-card-head">
                    <div>
                        <h2>Produits commandés</h2>
                        <p class="saas-card-description">{{ $order->items->count() }} article(s).</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table text-nowrap w-100">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th style="text-align: right;">Prix unitaire</th>
                                <th style="text-align: center;">Qté</th>
                                <th style="text-align: right;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td style="text-align: right;">{{ number_format($item->unit_price, 0, ',', ' ') }} FCFA</td>
                                <td style="text-align: center;">{{ $item->quantity }}</td>
                                <td style="text-align: right; font-weight: 700;">{{ number_format($item->total_price, 0, ',', ' ') }} FCFA</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="border-top: 2px solid var(--ds-border-strong);">
                                <th colspan="3" style="text-align: right; color: var(--ds-text-muted);">Sous-total</th>
                                <th style="text-align: right;">{{ number_format($order->subtotal, 0, ',', ' ') }} FCFA</th>
                            </tr>
                            <tr>
                                <th colspan="3" style="text-align: right; color: var(--ds-text-muted);">Taxe</th>
                                <th style="text-align: right;">{{ number_format($order->tax, 0, ',', ' ') }} FCFA</th>
                            </tr>
                            <tr style="border-top: 2px solid var(--ds-border-strong);">
                                <th colspan="3" style="text-align: right; font-size: 1rem;">Total</th>
                                <th style="text-align: right; font-size: 1.1rem; color: var(--ds-accent);">{{ number_format($order->total, 0, ',', ' ') }} FCFA</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    $(function() {
        $('#executeOrder').on('click', function() {
            Swal.fire({
                icon: 'question', title: 'Passer cette commande en vente ?',
                html: '<p class="saas-confirm-copy">Le stock et les caisses seront mis à jour seulement après la création réussie de la vente.</p>',
                showCancelButton: true, confirmButtonText: 'Oui, créer la vente', cancelButtonText: 'Annuler',
                buttonsStyling: false, customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-primary', cancelButton: 'saas-btn saas-btn-ghost' },
                showLoaderOnConfirm: true, allowOutsideClick: () => !Swal.isLoading(), allowEscapeKey: () => !Swal.isLoading(),
                preConfirm: function() {
                    return $.post("{{ route('ecommerce.orders.execute', $order->id) }}", {_token: "{{ csrf_token() }}"})
                        .catch(function(xhr) { Swal.showValidationMessage(xhr.responseJSON?.msg || 'Impossible de créer la vente.'); return false; });
                }
            }).then(function(result) {
                if (result.isConfirmed && result.value) {
                    Swal.fire({icon: 'success', title: result.value.title, text: result.value.msg}).then(() => location.reload());
                }
            });
        });

        $('#cancelOrder').on('click', function() {
            Swal.fire({
                icon: 'warning', title: 'Annuler cette commande ?',
                input: 'textarea', inputLabel: 'Motif de l\'annulation', inputPlaceholder: 'Le client n\'a pas confirmé…',
                inputAttributes: {maxlength: 500},
                inputValidator: value => !value?.trim() ? 'Indiquez le motif de l\'annulation.' : undefined,
                showCancelButton: true, confirmButtonText: 'Oui, annuler', cancelButtonText: 'Retour',
                confirmButtonColor: '#dc3545', showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(), allowEscapeKey: () => !Swal.isLoading(),
                preConfirm: function(reason) {
                    return $.post("{{ route('ecommerce.orders.cancel', $order->id) }}", {_token: "{{ csrf_token() }}", reason: reason})
                        .catch(function(xhr) { Swal.showValidationMessage(xhr.responseJSON?.msg || 'Impossible d\'annuler cette commande.'); return false; });
                }
            }).then(function(result) {
                if (result.isConfirmed && result.value) {
                    Swal.fire({icon: 'success', title: result.value.title, text: result.value.msg}).then(() => location.reload());
                }
            });
        });
    });
    </script>
    @endpush
@endsection
