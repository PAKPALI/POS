/* Expérience d'envoi d'image produit/pack : aperçu local et progression réseau. */
(function (window, document) {
    'use strict';

    function formatSize(bytes) {
        if (!bytes) return '0 Ko';
        var units = ['o', 'Ko', 'Mo'];
        var index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
        var value = bytes / Math.pow(1024, index);
        return (value >= 10 || index === 0 ? Math.round(value) : value.toFixed(1).replace('.', ',')) + ' ' + units[index];
    }

    function setup(root) {
        if (!root || root.dataset.imageUploadReady === 'true') return;

        var input = root.querySelector('[data-image-upload-input]');
        var preview = root.querySelector('[data-image-upload-preview]');
        var image = root.querySelector('[data-image-upload-image]');
        var name = root.querySelector('[data-image-upload-name]');
        var meta = root.querySelector('[data-image-upload-meta]');
        var clear = root.querySelector('[data-image-upload-clear]');
        var progress = root.querySelector('[data-image-upload-progress]');
        var progressBar = root.querySelector('[data-image-upload-progress-bar]');
        var progressText = root.querySelector('[data-image-upload-progress-text]');
        var objectUrl = null;
        root.dataset.imageUploadReady = 'true';

        function revoke() {
            if (objectUrl) window.URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }

        function reset() {
            revoke();
            input.value = '';
            preview.hidden = true;
            clear.hidden = true;
            root.classList.remove('has-file', 'has-error', 'is-uploading');
            root.removeAttribute('data-upload-error');
            if (progress) progress.hidden = true;
        }

        function update(file) {
            revoke();
            root.classList.remove('has-error');
            root.removeAttribute('data-upload-error');

            if (!file) {
                preview.hidden = true;
                clear.hidden = true;
                root.classList.remove('has-file');
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                input.value = '';
                preview.hidden = true;
                clear.hidden = true;
                root.classList.remove('has-file');
                root.classList.add('has-error');
                root.dataset.uploadError = 'Cette image dépasse la limite de 10 Mo.';
                return;
            }

            objectUrl = window.URL.createObjectURL(file);
            image.src = objectUrl;
            name.textContent = file.name;
            meta.textContent = formatSize(file.size) + ' · prête à être optimisée';
            preview.hidden = false;
            clear.hidden = false;
            root.classList.add('has-file');
        }

        input.addEventListener('change', function () { update(input.files && input.files[0]); });
        clear.addEventListener('click', reset);

        root._imageUpload = {
            hasFile: function () { return !!(input.files && input.files.length); },
            start: function () {
                if (!this.hasFile()) return;
                root.classList.add('is-uploading');
                progress.hidden = false;
                progressBar.style.width = '0%';
                progressText.textContent = 'Envoi de l’image… 0 %';
            },
            setProgress: function (loaded, total) {
                if (!total) return;
                var percentage = Math.min(100, Math.round((loaded / total) * 100));
                progressBar.style.width = percentage + '%';
                progressText.textContent = percentage < 100
                    ? 'Envoi de l’image… ' + percentage + ' %'
                    : 'Optimisation sécurisée en cours…';
            },
            finish: function () {
                root.classList.remove('is-uploading');
                if (this.hasFile()) {
                    progressBar.style.width = '100%';
                    progressText.textContent = 'Envoi terminé.';
                }
            },
            reset: reset
        };
    }

    function initialize(scope) {
        (scope || document).querySelectorAll('[data-image-upload]').forEach(setup);
    }

    window.ProductImageUpload = {
        init: initialize,
        forForm: function (form) {
            var root = form && form.querySelector('[data-image-upload]');
            return root && root._imageUpload ? root._imageUpload : null;
        }
    };

    document.addEventListener('DOMContentLoaded', function () { initialize(document); });
})(window, document);
