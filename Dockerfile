FROM php:7.4-apache

RUN a2enmod rewrite

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libicu-dev \
 && docker-php-ext-configure intl \
 && docker-php-ext-install intl pdo pdo_mysql mysqli zip \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

RUN chown -R www-data:www-data application/logs uploads || true
