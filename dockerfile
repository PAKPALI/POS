FROM php:8.2-apache

# Installer les extensions nécessaires pour Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl gd

# Installer Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Copier les fichiers de votre projet dans le conteneur
COPY . /var/www/html

# Définir le répertoire de travail
WORKDIR /var/www/html

# Donner les permissions au dossier de stockage et de cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Exposer le port utilisé par Laravel
EXPOSE 80

# Utiliser root pour exécuter composer install
USER root
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Revenir à l'utilisateur non-root
USER www-data

# Créer un utilisateur non-root pour Composer
RUN useradd -m composeruser
USER composeruser

# Démarrer Apache
CMD ["apache2-foreground"]
