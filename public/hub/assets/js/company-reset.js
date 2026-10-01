document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('companyResetForm');
    if (!form) return;
    const config = JSON.parse(document.getElementById('companyResetConfig').textContent);
    const catalog = config.catalog;
    const options = [...document.querySelectorAll('[data-reset-option]')];
    const direct = new Set();
    const terms = document.getElementById('resetTerms');
    const password = document.getElementById('resetPassword');
    const feedback = document.getElementById('companyResetFeedback');
    const requestButton = document.getElementById('requestResetCode');
    const verification = document.getElementById('companyResetVerification');
    const codeForm = document.getElementById('companyResetCodeForm');
    let selected = new Set();
    let challengeId = null;
    let busy = false;
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    function fail(error) {
        feedback.hidden = false;
        feedback.textContent = error.message || 'La demande n’a pas pu être traitée. Réessayez.';
        feedback.scrollIntoView({ block: 'nearest' });
    }
    function summarize(target, summary) {
        target.replaceChildren();
        const items = Object.values(summary);
        if (!items.length) { target.textContent = 'Aucune rubrique sélectionnée.'; return; }
        const title = document.createElement('strong');
        title.textContent = items.length + ' rubrique(s) concernée(s)';
        target.append(title);
        const list = document.createElement('ul');
        items.forEach(item => { const li = document.createElement('li'); li.textContent = item.label + ' · ' + item.count; list.append(li); });
        target.append(list);
    }
    function refresh() {
        selected = new Set(direct);
        const reasons = {};
        direct.forEach(root => {
            const visited = new Set([root]);
            const pending = [root];
            while (pending.length) {
                const key = pending.pop();
                catalog[key].depends.forEach(dependency => {
                    if (!catalog[dependency] || visited.has(dependency)) return;
                    visited.add(dependency);
                    selected.add(dependency);
                    (reasons[dependency] ||= new Set()).add(catalog[root].label);
                    pending.push(dependency);
                });
            }
        });
        options.forEach(option => {
            option.checked = selected.has(option.value);
            const automatic = option.checked && !direct.has(option.value);
            option.disabled = automatic;
            const card = option.closest('[data-reset-card]');
            card.classList.toggle('is-selected', option.checked);
            const reason = card.querySelector('[data-reset-reason]');
            reason.hidden = !automatic;
            reason.textContent = automatic ? 'Ajouté automatiquement à cause de : ' + [...(reasons[option.value] || [])].join(', ') + '. Pour le retirer, décochez les rubriques qui en dépendent.' : '';
        });
        summarize(document.getElementById('companyResetSummary'), Object.fromEntries([...selected].map(key => [key, catalog[key]])));
        requestButton.disabled = busy || !selected.size || !terms.checked || !password.value;
    }
    options.forEach(option => option.addEventListener('change', () => { option.checked ? direct.add(option.value) : direct.delete(option.value); refresh(); }));
    terms.addEventListener('change', refresh);
    password.addEventListener('input', refresh);
    async function post(url, payload) {
        const response = await fetch(url, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify(payload) });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(Object.values(data.errors || {})[0]?.[0] || data.message || 'Le serveur est momentanément inaccessible. Réessayez.');
        return data;
    }
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (busy || !terms.checked || !selected.size || !password.value) return;
        busy = true; feedback.hidden = true;
        try {
            const data = await window.ServerButtonLoader.withLoader(requestButton, () => post(config.challengeUrl, { selection: [...selected], terms: true, current_password: password.value }), 'Envoi du code…');
            challengeId = data.id;
            form.hidden = true; verification.hidden = false;
            password.value = '';
            document.getElementById('companyResetCodeMessage').textContent = data.message;
            summarize(document.getElementById('companyResetFinalSummary'), data.summary);
            document.getElementById('resetCode').focus();
        } catch (error) { fail(error); }
        finally { busy = false; refresh(); }
    });
    document.getElementById('cancelCompanyReset').addEventListener('click', () => {
        if (busy) return;
        challengeId = null; form.hidden = false; verification.hidden = true; codeForm.reset(); terms.checked = false; refresh();
    });
    codeForm.addEventListener('submit', async event => {
        event.preventDefault();
        if (busy || !challengeId || !codeForm.reportValidity()) return;
        busy = true; feedback.hidden = true;
        document.getElementById('cancelCompanyReset').disabled = true;
        try {
            const data = await window.ServerButtonLoader.withLoader(event.submitter, () => post(config.executeUrl, { id: challengeId, code: document.getElementById('resetCode').value, terms: true }), 'Réinitialisation…');
            await Swal.fire({ icon: 'success', title: 'Réinitialisation terminée', text: data.message, confirmButtonText: 'Revenir au profil', buttonsStyling: false, customClass: { popup: 'saas-swal', confirmButton: 'saas-btn saas-btn-primary' } });
            window.location.assign(data.redirect);
        } catch (error) { fail(error); }
        finally { busy = false; document.getElementById('cancelCompanyReset').disabled = false; }
    });
    refresh();
});
