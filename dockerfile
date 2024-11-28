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

# Copier le fichier composer.phar si nécessaire
# COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Copier les fichiers de votre projet dans le conteneur
COPY . /var/www/html

# Définir le répertoire de travail
WORKDIR /var/www/html

# Donner les permissions au dossier de stockage et de cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Exposer le port utilisé par Laravel
EXPOSE 80

# Installer Composer via une méthode sûre et vérifier l'installation
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Installer les dépendances Composer
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Démarrer Apache
CMD ["apache2-foreground"]
