#!/bin/sh
set -e

cd /var/www/html

# Ensure storage directories exist
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache/data
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Fix permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Create storage symlink
php artisan storage:link --force 2>/dev/null || true

# Clear any stale caches (do NOT re-cache - closures break route:cache)
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# Wait for database using PHP (no external tools needed)
echo "Waiting for database at ${DB_HOST:-postgres}:${DB_PORT:-5432}..."
for i in $(seq 1 30); do
    if php -r "try { new PDO('pgsql:host='.\$_SERVER['DB_HOST'].';port='.\$_SERVER['DB_PORT'].';dbname='.\$_SERVER['DB_DATABASE'], \$_SERVER['DB_USERNAME'], \$_SERVER['DB_PASSWORD']); echo 'ok'; } catch(Exception \$e) { exit(1); }" 2>/dev/null; then
        echo "Database is ready!"
        break
    fi
    echo "Attempt $i/30 - waiting..."
    sleep 2
done

# Run migrations
php artisan migrate --force 2>/dev/null || echo "Migration skipped"

echo "Starting services..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisor.conf
