# --- Stage 1: Node.js for Frontend Assets ---
FROM node:18-alpine AS builder

WORKDIR /app
COPY package*.json ./
RUN npm install

COPY . .
RUN npm run build

# --- Stage 2: PHP FPM with Dependencies ---
FROM php:7.4.33-fpm-bullseye

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    locales \
    zip \
    unzip \
    git \
    curl

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath xml
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# Install composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Copy existing application directory contents
COPY . /var/www

# Copy built assets from Stage 1
COPY --from=builder /app/public/build /var/www/public/build
COPY --from=builder /app/public/js /var/www/public/js
COPY --from=builder /app/public/css /var/www/public/css

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create necessary directories and set permissions
RUN mkdir -p /var/www/storage/app/purify \
    && mkdir -p /var/www/storage/framework/cache \
    && mkdir -p /var/www/storage/framework/sessions \
    && mkdir -p /var/www/storage/framework/views \
    && mkdir -p /var/www/storage/logs \
    && chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Change current user to www-data
USER www-data

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]