FROM php:8.4-fpm

# Install required system packages and PHP extensions
RUN apt-get update \
    && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo_pgsql pgsql mbstring bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . .

# Verify extensions are available
RUN php -m >/dev/null

CMD ["php-fpm"]

