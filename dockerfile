# Utilisez une image PHP avec Apache ou Nginx
FROM php:8.2-apache

# Activez l'extension PDO et les extensions nécessaires pour Laravel
RUN docker-php-ext-install pdo pdo_mysql

# Copier le code source de l'application dans le conteneur
COPY . /var/www/html

# Installez Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Donner des permissions au dossier des applications (initialement, la copie peut échouer si des permissions sont insuffisantes)
RUN chmod -R 775 /var/www/html

# Installer les dépendances Composer
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader

# Créez les répertoires nécessaires si ce n'est pas déjà fait
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache

# Changer les permissions des répertoires storage et cache pour que l'utilisateur web puisse y écrire
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Exposer le port 80 pour Apache
EXPOSE 80

# Démarrer Apache en mode premier plan
CMD ["apache2-foreground"]
