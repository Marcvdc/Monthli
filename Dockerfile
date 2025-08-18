FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (install mbstring first as other extensions may depend on it)
RUN docker-php-ext-install mbstring \
    && docker-php-ext-install pdo_pgsql pgsql exif pcntl bcmath gd intl zip dom xml \
    && pecl install redis \
    && docker-php-ext-enable redis

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files with proper ownership
COPY --chown=www-data:www-data . /var/www

# Set proper permissions for Laravel directories
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Change current user to www
USER www-data

# Verify extensions are available
RUN php -m >/dev/null

CMD ["php-fpm"]
