#!/bin/bash
set -e

# Wait for database to be ready
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
