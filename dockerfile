FROM php:8.1-fpm

# Installer les dépendances nécessaires
RUN apt-get update && apt-get install -y \
    git curl libzip-dev zip unzip \
    && docker-php-ext-install pdo pdo_mysql zip

# Installer Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Configurer le dossier de travail
WORKDIR /var/www/html

# Copier les fichiers de l'application
COPY . .

# Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Définir les permissions
RUN chown -R www-data:www-data /var/www/html

# Exposer le port
EXPOSE 8000

# Commande par défaut pour le conteneur
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
