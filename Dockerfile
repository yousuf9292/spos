FROM php:8.2-apache

# Enable Apache rewrite
RUN a2enmod rewrite

# System deps + PHP extensions commonly needed
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev \
  && docker-php-ext-install intl pdo pdo_mysql zip \
  && rm -rf /var/lib/apt/lists/*

# Set Apache DocumentRoot to /public (CI4)
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html
COPY . /var/www/html

# Install Composer deps
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# Permissions (adjust if needed)
RUN chown -R www-data:www-data /var/www/html/writable
