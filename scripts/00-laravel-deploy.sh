#!/usr/bin/env bash

# Déploiement Laravel 12 de PRO-SELLER.
#
# Variables facultatives :
#   APP_DIR=/var/www/html
#   PHP_BIN=php
#   COMPOSER_BIN=composer
#   NPM_BIN=npm
#   RUN_MIGRATIONS=true|false
#   RUN_FRONTEND_BUILD=true|false
#
# Exemple :
#   APP_DIR=/home/UTILISATEUR_CPANEL/proseller bash scripts/00-laravel-deploy.sh

set -Eeuo pipefail

APP_DIR="${APP_DIR:-/var/www/html}"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
NPM_BIN="${NPM_BIN:-npm}"
RUN_MIGRATIONS="${RUN_MIGRATIONS:-true}"
RUN_FRONTEND_BUILD="${RUN_FRONTEND_BUILD:-true}"

log() {
    printf '[deploy] %s\n' "$1"
}

fail() {
    printf '[deploy][erreur] %s\n' "$1" >&2
    exit 1
}

command -v "$PHP_BIN" >/dev/null 2>&1 || fail "PHP introuvable : $PHP_BIN"
command -v "$COMPOSER_BIN" >/dev/null 2>&1 || fail "Composer introuvable : $COMPOSER_BIN"

cd "$APP_DIR"

log "Vérification de la version PHP"
"$PHP_BIN" -v

log "Installation des dépendances PHP verrouillées"
"$COMPOSER_BIN" install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction

if [[ "$RUN_FRONTEND_BUILD" == "true" ]]; then
    if [[ -f package.json ]]; then
        command -v "$NPM_BIN" >/dev/null 2>&1 || fail "npm est requis pour compiler les assets frontend"

        log "Installation des dépendances frontend verrouillées"
        "$NPM_BIN" ci --no-audit --no-fund

        log "Compilation des assets frontend"
        "$NPM_BIN" run build
    else
        log "Aucun package.json : compilation frontend ignorée"
    fi
fi

log "Suppression du marqueur Vite de développement"
rm -f public/hot

log "Contrôle du design system SaaS"
"$PHP_BIN" artisan ui:lint

if [[ "$RUN_MIGRATIONS" == "true" ]]; then
    log "Exécution des migrations en mode non interactif"
    "$PHP_BIN" artisan migrate --force
else
    log "Migrations ignorées (RUN_MIGRATIONS=$RUN_MIGRATIONS)"
fi

log "Nettoyage des caches existants"
"$PHP_BIN" artisan optimize:clear

log "Reconstruction des caches compatibles avec le projet"
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan event:cache
"$PHP_BIN" artisan view:cache

# route:cache est volontairement omis : le projet contient encore un nom de
# route dupliqué (codePromo.pdf). Il sera réactivé après correction et test.

log "Demande de redémarrage des workers de queue"
"$PHP_BIN" artisan queue:restart

log "Vérifications post-déploiement"
"$PHP_BIN" artisan about
"$PHP_BIN" artisan migrate:status
"$PHP_BIN" artisan schedule:list
"$PHP_BIN" artisan queue:failed

log "Déploiement terminé"
