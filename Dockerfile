# Stage 1: Build Aset Frontend (Vite)
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: Setup PHP & Composer (PHP 8.3 Required for Laravel 13)
FROM php:8.3-fpm-alpine

# Install dependensi sistem & ekstensi PHP
RUN apk add --no-cache zip libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev icu-dev oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql zip gd bcmath opcache exif intl pcntl

# Konfigurasi php.ini produksi
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 25M/g' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/post_max_size = 8M/post_max_size = 30M/g' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/memory_limit = 128M/memory_limit = 512M/g' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/max_execution_time = 30/max_execution_time = 300/g' "$PHP_INI_DIR/php.ini"

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy seluruh file project
COPY . .

# Copy hasil build Vite dari Stage 1
COPY --from=frontend /app/public/build ./public/build

# Install dependensi PHP
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Pastikan folder storage khusus DomPDF dan cache dibuat
RUN mkdir -p /var/www/html/storage/fonts \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/app/public \
    /var/www/html/storage/app/private/izins \
    /var/www/html/bootstrap/cache

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache