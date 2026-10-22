FROM php:8.3-apache

# system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Apache mod_rewrite
RUN a2enmod rewrite

COPY docker/php/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html