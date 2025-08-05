FROM php:7.2.17-fpm

LABEL maintainer="NutriChain Logistics Development Team"

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure intl \
    && docker-php-ext-install \
        intl \
        pdo \
        pdo_pgsql \
        zip \
    && pecl install redis-4.2.0 \
    && docker-php-ext-enable redis opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data app/cache app/logs app/var

EXPOSE 9000