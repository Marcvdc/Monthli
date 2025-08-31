# Multi-stage build for optimized production image
FROM php:8.4-fpm AS base

# Install runtime system dependencies including gosu for user switching
RUN apt-get update && apt-get install -y \
    libpng16-16 \
    libonig5 \
    libxml2 \
    libpq5 \
    libicu74 \
    libzip4 \
    gosu \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* || \
    # Fallback to generic package names if specific versions fail
    (apt-get update && apt-get install -y \
    libpng16-16 \
    libonig5 \
    libxml2 \
    libpq5 \
    libicu-dev \
    libzip-dev \
    gosu \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*)

# Install PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install mbstring pdo_pgsql pgsql exif pcntl bcmath gd intl zip dom xml \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get purge -y \
    libpng-dev libonig-dev libxml2-dev libpq-dev libicu-dev libzip-dev \
    && apt-get autoremove -y \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

# ===== BUILD STAGE =====
FROM base AS build

# Install build dependencies including Node.js
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    curl \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Get Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy composer files first for layer caching
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --no-dev

# Copy package.json for npm dependencies
COPY package.json package-lock.json* ./
RUN npm ci --production=false

# Copy application files
COPY . .

# Build frontend assets
RUN npm run build

# Optimize composer autoloader and remove dev dependencies
RUN composer install --optimize-autoloader --no-dev --no-scripts \
    && composer clear-cache

# ===== PRODUCTION STAGE =====
FROM base AS production

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Copy application from build stage
COPY --from=build --chown=www-data:www-data /var/www /var/www

# Create Laravel directories with proper permissions
RUN mkdir -p /var/www/storage/logs \
    /var/www/storage/framework/cache \
    /var/www/storage/framework/sessions \
    /var/www/storage/framework/views \
    /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Note: Container starts as root to fix permissions, then switches to www-data via entrypoint

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
