FROM php:8.4-cli
WORKDIR /var/www/html
COPY . .
RUN php -m >/dev/null
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
