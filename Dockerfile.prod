# ==============================================================================
# Multi-Stage Production Dockerfile for E-Management Polbangtan-mlg
# Target: Linux Debian VPS (PHP 8.3 FPM + Nginx + Redis + MySQL 8.0)
# ==============================================================================

# ------------------------------------------------------------------------------
# Stage 1: Build Frontend Assets (Vite, Tailwind v4, Flowbite, Alpine.js)
# ------------------------------------------------------------------------------
FROM node:20-alpine AS frontend
WORKDIR /app

# Cache dependency layer
COPY package*.json ./
RUN npm ci || npm install

# Copy source and compile production assets
COPY . .
RUN npm run build

# ------------------------------------------------------------------------------
# Stage 2: Production PHP 8.3 FPM Runtime
# ------------------------------------------------------------------------------
FROM php:8.3-fpm-alpine AS production

# Install build tools & runtime system dependencies
RUN apk add --no-cache \
    zip \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    oniguruma-dev \
    autoconf \
    gcc \
    g++ \
    make \
    linux-headers

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        zip \
        gd \
        bcmath \
        opcache \
        exif \
        intl \
        pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del autoconf gcc g++ make linux-headers

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application files
COPY . .

# Copy compiled frontend assets from Stage 1
COPY --from=frontend /app/public/build ./public/build

# Copy custom PHP & Opcache configurations
COPY docker/php/custom.ini $PHP_INI_DIR/conf.d/99-custom.ini
COPY docker/php/opcache.ini $PHP_INI_DIR/conf.d/99-opcache.ini

# Install PHP dependencies for production
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Create required directories for DomPDF fonts, private permits, sessions, views, logs
RUN mkdir -p \
    storage/fonts \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    storage/app/public \
    storage/app/private/izins \
    bootstrap/cache

# Fix ownership and permissions for www-data (UID 82)
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php-fpm"]
