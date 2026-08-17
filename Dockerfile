# Stage 1: Build Aset Frontend (Vite)
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: Setup PHP & Composer
FROM php:8.2-fpm-alpine

# Install dependensi sistem & ekstensi PHP
RUN apk add --no-cache zip libzip-dev libpng-dev \
    && docker-php-ext-install pdo_mysql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy seluruh file project
COPY . .

# Copy hasil build Vite dari Stage 1
COPY --from=frontend /app/public/build ./public/build

# Install dependensi PHP
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache