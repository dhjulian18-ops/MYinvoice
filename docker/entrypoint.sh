#!/bin/bash
set -e

echo "=== Starting MyInvoice API ==="

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    echo "Generating APP_KEY..."
    php artisan key:generate --force
fi

# Cache configuration
echo "Caching config & routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage symlink
php artisan storage:link 2>/dev/null || true

# Set permissions
chmod -R 775 /var/www/storage /var/www/bootstrap/cache
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Run migrations
echo "Running migrations..."
php artisan migrate --force

echo "=== Server starting on port 8080 ==="
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
