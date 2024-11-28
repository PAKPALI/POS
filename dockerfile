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

# Copier Composer depuis l'image officielle
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Copier les fichiers de votre projet dans le conteneur
COPY . /var/www/html

# Définir le répertoire de travail
WORKDIR /var/www/html

# Donner les permissions appropriées aux répertoires de Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Créer un utilisateur non-root pour Composer
RUN useradd -m composeruser

# Passer à l'utilisateur non-root pour les prochaines commandes
USER composeruser

# Exposer le port utilisé par Laravel
EXPOSE 80

# Installer les dépendances via Composer
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Redémarrer Apache après installation de Composer
RUN apache2ctl restart
