@extends('layouts.layout')

@section('content')
<div class="container">
    <h1 class="page-header">Détail commande #{{ $order->code }}</h1>
    <hr class="mb-4">

    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-3">
                <div class="card-body">
                    <h4>Informations client</h4>
                    <table class="table table-borderless">
                        <tr><td>Nom :</td><td>{{ $order->customer_name }}</td></tr>
                        <tr><td>Téléphone :</td><td>{{ $order->customer_phone }}</td></tr>
                        <tr><td>E-mail :</td><td>{{ $order->customer_email ?? '-' }}</td></tr>
                        <tr><td>Adresse :</td><td>{{ $order->customer_address ?? '-' }}</td></tr>
                        <tr>
                            <td>Localisation :</td>
                            <td>
                                @if($order->delivery_location_url)
                                    <a href="{{ $order->delivery_location_url }}" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener noreferrer">
                                        <i class="fas fa-map-marker-alt me-1"></i> Ouvrir dans Google Maps
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr><td>Notes :</td><td>{{ $order->notes ?? '-' }}</td></tr>
                    </table>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h4>Traitement de la commande</h4>
                    @php
                        $statusLabels = ['pending' => 'En attente', 'confirmed' => 'Confirmée', 'converted' => 'Passée en vente', 'cancelled' => 'Annulée'];
                        $statusColors = ['pending' => 'warning', 'confirmed' => 'info', 'converted' => 'success', 'cancelled' => 'danger'];
                    @endphp
                    <p>Statut : <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">{{ $statusLabels[$order->status] ?? ucfirst($order->status) }}</span></p>

                    @if($order->status === 'converted' && $order->sale)
                        <div class="alert alert-success mb-0">
                            Vente <strong>#{{ $order->sale->code }}</strong> créée le {{ $order->converted_at?->format('d/m/Y à H:i') }}
                            par {{ $order->convertedBy?->name ?? 'un utilisateur supprimé' }}.
                        </div>
                    @elseif($order->status === 'cancelled')
                        <div class="alert alert-danger mb-0">
                            Annulée le {{ $order->cancelled_at?->format('d/m/Y à H:i') }} par {{ $order->cancelledBy?->name ?? 'un utilisateur supprimé' }}.<br>
                            <strong>Motif :</strong> {{ $order->cancellation_reason }}
                        </div>
                    @elseif(in_array($order->status, ['pending', 'confirmed'], true))
                        <p class="text-muted">La commande ne modifie pas le stock. Après confirmation du client, passez-la en vente pour diminuer le stock et alimenter les caisses.</p>
                        <div class="d-flex flex-wrap gap-2">
                            @if($canConvertToSale)
                                <button type="button" id="executeOrder" class="btn btn-success" data-no-server-loader>
                                    <i class="fas fa-cash-register me-1"></i> Passer en vente
                                </button>
                            @endif
                            <button type="button" id="cancelOrder" class="btn btn-danger" data-no-server-loader>
                                <i class="fas fa-ban me-1"></i> Annuler la commande
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card mb-3">
                <div class="card-body">
                    <h4>Produits commandés</h4>
                    <table class="table">
                        <thead><tr><th>Produit</th><th>Prix unitaire</th><th>Quantité</th><th>Total</th></tr></thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ number_format($item->unit_price, 0, ',', ' ') }} FCFA</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->total_price, 0, ',', ' ') }} FCFA</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr><th colspan="3" class="text-end">Sous-total :</th><th>{{ number_format($order->subtotal, 0, ',', ' ') }} FCFA</th></tr>
                            <tr><th colspan="3" class="text-end">Taxe :</th><th>{{ number_format($order->tax, 0, ',', ' ') }} FCFA</th></tr>
                            <tr><th colspan="3" class="text-end">Total :</th><th>{{ number_format($order->total, 0, ',', ' ') }} FCFA</th></tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(function() {
    $('#executeOrder').on('click', function() {
        Swal.fire({
            icon: 'question', title: 'Passer cette commande en vente ?',
            text: 'Le stock et les caisses seront mis à jour seulement après la création réussie de la vente.',
            showCancelButton: true, confirmButtonText: 'Oui, créer la vente', cancelButtonText: 'Annuler',
            confirmButtonColor: '#198754', showLoaderOnConfirm: true,
            allowOutsideClick: () => !Swal.isLoading(), allowEscapeKey: () => !Swal.isLoading(),
            preConfirm: function() {
                return $.post("{{ route('ecommerce.orders.execute', $order->id) }}", {_token: "{{ csrf_token() }}"})
                    .catch(function(xhr) {
                        Swal.showValidationMessage(xhr.responseJSON?.msg || 'Impossible de créer la vente.');
                        return false;
                    });
            }
        }).then(function(result) {
            if (result.isConfirmed && result.value) {
                Swal.fire({icon: 'success', title: result.value.title, text: result.value.msg}).then(() => location.reload());
            }
        });
    });

    $('#cancelOrder').on('click', function() {
        Swal.fire({
            icon: 'warning', title: 'Annuler cette commande ?', input: 'textarea',
            inputLabel: 'Motif de l’annulation', inputPlaceholder: 'Le client n’a pas confirmé après notre appel.',
            inputAttributes: {maxlength: 500},
            inputValidator: value => !value?.trim() ? 'Indiquez le motif de l’annulation.' : undefined,
            showCancelButton: true, confirmButtonText: 'Oui, annuler', cancelButtonText: 'Retour',
            confirmButtonColor: '#dc3545', showLoaderOnConfirm: true,
            allowOutsideClick: () => !Swal.isLoading(), allowEscapeKey: () => !Swal.isLoading(),
            preConfirm: function(reason) {
                return $.post("{{ route('ecommerce.orders.cancel', $order->id) }}", {_token: "{{ csrf_token() }}", reason: reason})
                    .catch(function(xhr) {
                        Swal.showValidationMessage(xhr.responseJSON?.msg || 'Impossible d’annuler cette commande.');
                        return false;
                    });
            }
        }).then(function(result) {
            if (result.isConfirmed && result.value) {
                Swal.fire({icon: 'success', title: result.value.title, text: result.value.msg}).then(() => location.reload());
            }
        });
    });
});
</script>
@endsection
