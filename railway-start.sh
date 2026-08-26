#!/bin/sh
# Railway startup: migrations + framework caches + OPcache-friendly serve.
# Caches are built at RUNTIME because Railway injects env vars after build.
set -e

echo "Running migrations..."
php artisan migrate --force

echo "Caching config, routes, views, events..."
php artisan optimize

echo "Starting server..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}" --no-reload
