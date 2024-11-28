# Utiliser une image PHP officielle avec Apache
FROM php:8.2-apache

# Installer les extensions nécessaires pour Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl gd

# Installer Composer en copiant le binaire depuis l'image officielle de Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Copier les fichiers du projet dans le conteneur
COPY . /var/www/html

# Définir le répertoire de travail
WORKDIR /var/www/html

# Créer un utilisateur non-root (composeruser) pour exécuter Composer
RUN useradd -m composeruser

# Passer à l'utilisateur non-root
USER composeruser

# Autoriser l'exécution de Composer en tant que superutilisateur (optionnel si vous voulez forcer Composer à s'exécuter en tant que root)
ENV COMPOSER_ALLOW_SUPERUSER=1

# Donner les permissions appropriées aux répertoires de Laravel (storage, cache)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Installer les dépendances Composer en mode production (pas de dépendances de développement)
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Exposer le port utilisé par Apache (80)
EXPOSE 80

# Démarrer Apache en mode non-daemon
CMD ["apache2-foreground"]
