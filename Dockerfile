FROM php:8.2-apache

# Enable Apache rewrite
RUN a2enmod rewrite

# Install system dependencies + PHP extensions
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libicu-dev \
 && docker-php-ext-install pdo pdo_mysql mysqli zip intl \
 && rm -rf /var/lib/apt/lists/*

# Apache config
WORKDIR /var/www/html
COPY . /var/www/html

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install PHP dependencies (vendor/)
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# Permissions (CI3 writable folders)
RUN chown -R www-data:www-data \
    application/logs \
    uploads || true
