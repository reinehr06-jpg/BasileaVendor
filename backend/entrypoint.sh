#!/bin/sh

# Wait for database
echo "Waiting for database..."
# (Assuming postgres service is named 'postgres')
while ! nc -z $DB_HOST $DB_PORT; do
  sleep 1
done

echo "Database is up - running migrations"
php artisan migrate --force

echo "Starting Supervisor"
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisor.conf
