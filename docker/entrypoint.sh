#!/bin/bash
set -e

# Fix storage permissions - must run as root first
if [ "$(id -u)" = "0" ]; then
    echo "🔧 Fixing storage permissions..."
    
    # Ensure storage directories exist with correct permissions
    mkdir -p /var/www/storage/{app/{private,public},framework/{cache/data,sessions,views},logs}
    mkdir -p /var/www/bootstrap/cache
    
    # Set ownership and permissions
    chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache
    
    # Switch to www-data user and re-execute script
    echo "👤 Switching to www-data user..."
    exec gosu www-data "$0" "$@"
fi

# Now running as www-data user
# Generate app key if not set
if ! grep -q "^APP_KEY=base64:" /var/www/.env 2>/dev/null; then
    echo "🔑 Generating application encryption key..."
    php artisan key:generate --force
fi

echo "🔄 Waiting for database connection..."
while ! php artisan migrate:status >/dev/null 2>&1; do
    echo "⏳ Database not ready, waiting 2 seconds..."
    sleep 2
done

# Run migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Seed admin user if needed
echo "🌱 Seeding admin user..."
php artisan db:seed --class=AdminUserSeeder --force

# Clear and cache configs for production
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Application ready!"

# Execute the main command
exec "$@"
