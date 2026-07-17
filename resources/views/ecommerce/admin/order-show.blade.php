@extends('layouts.layout')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-12">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('ecommerce.orders.index') }}">Commandes</a></li>
                <li class="breadcrumb-item active">Commande #{{ $order->code }}</li>
            </ul>
            <h1 class="page-header">Detail commande #{{ $order->code }}</h1>
            <hr class="mb-4">

            <div class="row">
                <div class="col-xl-6">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h4>Informations client</h4>
                            <table class="table table-borderless">
                                <tr><td>Nom :</td><td>{{ $order->customer_name }}</td></tr>
                                <tr><td>Telephone :</td><td>{{ $order->customer_phone }}</td></tr>
                                <tr><td>Email :</td><td>{{ $order->customer_email ?? '-' }}</td></tr>
                                <tr><td>Adresse :</td><td>{{ $order->customer_address ?? '-' }}</td></tr>
                                <tr><td>Notes :</td><td>{{ $order->notes ?? '-' }}</td></tr>
                            </table>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <h4>Statut</h4>
                            <form id="statusForm">
                                @csrf
                                <div class="input-group">
                                    <select name="status" class="form-select">
                                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>En attente</option>
                                        <option value="confirmed" {{ $order->status == 'confirmed' ? 'selected' : '' }}>Confirmee</option>
                                        <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>En cours</option>
                                        <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Expediee</option>
                                        <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Livree</option>
                                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Annulee</option>
                                    </select>
                                    <button type="submit" class="btn btn-warning">Mettre a jour</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h4>Produits commandes</h4>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Produit</th>
                                        <th>Prix unitaire</th>
                                        <th>Quantite</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
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
                                    <tr>
                                        <th colspan="3" class="text-end">Sous-total :</th>
                                        <th>{{ number_format($order->subtotal, 0, ',', ' ') }} FCFA</th>
                                    </tr>
                                    <tr>
                                        <th colspan="3" class="text-end">Taxe :</th>
                                        <th>{{ number_format($order->tax, 0, ',', ' ') }} FCFA</th>
                                    </tr>
                                    <tr>
                                        <th colspan="3" class="text-end">Total :</th>
                                        <th>{{ number_format($order->total, 0, ',', ' ') }} FCFA</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(function() {
    $('#statusForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: "{{ route('ecommerce.orders.status', $order->id) }}",
            data: $(this).serialize(),
            success: function(data) {
                Swal.fire({
                    toast: true, position: 'top', icon: 'success',
                    title: data.title, showConfirmButton: false, timer: 3000, text: data.msg
                }).then(() => { location.reload(); });
            }
        });
    });
});
</script>
@endsection
