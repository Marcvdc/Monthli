#!/bin/bash
set -e

# Only fix permissions if running as root
if [ "$(id -u)" -eq 0 ]; then
    echo "🔧 Fixing storage permissions..."
    
    # Ensure storage directories exist with correct permissions
    mkdir -p /var/www/storage/{app/{private,public},framework/{cache/data,sessions,views},logs}
    mkdir -p /var/www/bootstrap/cache
    
    # Set ownership and permissions for mounted volumes
    chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
    
    # Ensure .env file is writable by www-data
    if [ -f "/var/www/.env" ]; then
        chown www-data:www-data /var/www/.env 2>/dev/null || true
        chmod 664 /var/www/.env 2>/dev/null || true
    fi
    
    # Always switch to www-data for everything
    echo "👤 Switching to www-data user..."
    exec gosu www-data "$0" "$@"
fi

# Now running as www-data user
echo "🔧 Development environment starting..."

# Handle setup-only flag for Laravel initialization
if [ "$1" = "--setup-only" ]; then
    shift
    SETUP_ONLY=true
else
    SETUP_ONLY=false
fi

# Generate app key if not set (check for empty or malformed key)
if [ -f "/var/www/.env" ]; then
    APP_KEY=$(grep "^APP_KEY=" /var/www/.env 2>/dev/null | cut -d'=' -f2-)
    if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ] || ! echo "$APP_KEY" | grep -q "^base64:[A-Za-z0-9+/=]\{44\}$"; then
        echo "🔑 Generating application encryption key..."
        # Use artisan to generate key directly (safer than sed on bind mount)
        php artisan key:generate --force
    fi
fi

# Install dependencies if needed (development only)
if [ ! -d "/var/www/vendor" ] || [ ! -f "/var/www/vendor/autoload.php" ]; then
    echo "📦 Installing composer dependencies..."
    composer install --no-interaction
fi

# Wait for database and run migrations in development
if [ -f "/var/www/.env" ]; then
    echo "⏳ Waiting for database connection..."
    until php artisan migrate:status >/dev/null 2>&1; do
        echo "   Database not ready, waiting..."
        sleep 2
    done
    
    echo "🗄️ Running database migrations..."
    php artisan migrate --force
    
    # Seed admin user if needed
    php artisan db:seed --class=AdminUserSeeder --force 2>/dev/null || true
fi

echo "✅ Development environment ready!"

# Execute the main command (only if not setup-only)
if [ "$SETUP_ONLY" = "false" ]; then
    exec "$@"
fi
