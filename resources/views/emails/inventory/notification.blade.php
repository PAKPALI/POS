<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Notification d’inventaire</title>
    @include('emails.design.emailStyle')
</head>
<body>
<div class="container">
    <div class="header">
        <h2>{{ $company->name ?? config('app.name') }}</h2>
        <p style="color:red;">Notification d’inventaire</p>
    </div>
    @php($isEntry = (int) $inventory->type === 1)
    <div class="info">
        <h3>{{ $isEntry ? 'Entrée de stock enregistrée' : 'Sortie de stock enregistrée' }}</h3>
        <p><strong>Produit :</strong> {{ $inventory->product?->name ?? 'Produit supprimé' }}</p>
        <p><strong>Effectué par :</strong> {{ $inventory->user?->name ?? 'Utilisateur inconnu' }}</p>
        @if($inventory->supplier)<p><strong>Fournisseur :</strong> {{ $inventory->supplier->name }}</p>@endif
        <p><strong>Quantité avant :</strong> {{ $inventory->qte_before }}</p>
        <p><strong>Quantité {{ $isEntry ? 'ajoutée' : 'retirée' }} :</strong> {{ $inventory->qte_added }}</p>
        <p><strong>Quantité après :</strong> {{ $inventory->qte_after }}</p>
        <p><strong>Date :</strong> {{ $inventory->created_at?->format('d/m/Y H:i') }}</p>
        @if($inventory->note)<p><strong>Note :</strong> {{ $inventory->note }}</p>@endif
    </div>
    @include('emails.design.emailFooter')
</div>
</body>
</html>
