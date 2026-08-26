<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Créer une entreprise — {{ config('app.name') }}</title>
    <link href="{{ asset('hub/assets/css/vendor.min.css') }}" rel="stylesheet">
    <link href="{{ asset('hub/assets/css/app.min.css') }}" rel="stylesheet">
</head>
<body class="bg-dark text-white">
<main class="container py-5" style="max-width: 760px">
    <a href="{{ route('companies.select') }}" class="text-theme text-decoration-none">← Retour aux entreprises</a>
    <h1 class="h2 mt-3 mb-1">Créer une nouvelle entreprise</h1>
    <p class="text-white text-opacity-50 mb-4">Renseignez les informations essentielles. Vous pourrez compléter la configuration plus tard.</p>

    <div id="companyError" class="alert alert-danger d-none"></div>
    <form id="createCompanyForm" class="card card-body">
        @csrf
        <div class="row g-3">
            <div class="col-md-6"><label for="name" class="form-label">Nom de l’entreprise</label><input id="name" name="name" class="form-control" required autofocus></div>
            <div class="col-md-6"><label for="email" class="form-label">E-mail</label><input id="email" name="email" type="email" class="form-control" required></div>
            <div class="col-md-6"><label for="number1" class="form-label">Téléphone principal</label><input id="number1" name="number1" class="form-control" required></div>
            <div class="col-md-6"><label for="adress" class="form-label">Adresse</label><input id="adress" name="adress" class="form-control" required></div>
            <div class="col-md-6">
                <label for="default_tax" class="form-label">Taxe sur les ventes (%) <span class="text-white text-opacity-50">— facultatif</span></label>
                <input id="default_tax" name="default_tax" type="number" min="0" max="100" step="0.01" class="form-control" placeholder="Ex. 18">
                <div class="form-text">Si renseignée, elle sera configurée automatiquement avec la caisse de taxe.</div>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('companies.select') }}" class="btn btn-outline-secondary">Annuler</a>
            <button id="createCompanyButton" class="btn btn-theme">Créer l’entreprise</button>
        </div>
    </form>
</main>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('createCompanyForm').addEventListener('submit', async function (event) {
    event.preventDefault();
    const button = document.getElementById('createCompanyButton');
    const error = document.getElementById('companyError');
    button.disabled = true;
    error.classList.add('d-none');

    try {
        const response = await fetch(@json(route('companies.store')), {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(event.target)
        });
        const data = await response.json();
        if (!response.ok || !data.status) throw new Error(data.msg || 'Impossible de créer l’entreprise.');

        const choice = await Swal.fire({
            icon: 'success',
            title: 'Entreprise créée',
            text: 'Voulez-vous basculer vers « ' + data.company_name + ' » maintenant ?',
            confirmButtonText: 'Oui, basculer',
            cancelButtonText: 'Non, voir mes entreprises',
            showCancelButton: true,
            confirmButtonColor: '#16a34a'
        });

        if (choice.isConfirmed) {
            const switchForm = document.createElement('form');
            switchForm.method = 'POST';
            switchForm.action = data.switch_url;
            switchForm.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">';
            document.body.appendChild(switchForm);
            switchForm.submit();
        } else {
            window.location.href = data.selection_url;
        }
    } catch (exception) {
        error.textContent = exception.message;
        error.classList.remove('d-none');
        button.disabled = false;
    }
});
</script>
<script src="{{ asset('hub/assets/js/server-button-loader.js') }}?v=20260826-2"></script>
</body>
</html>
