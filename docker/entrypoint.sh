#!/usr/bin/env bash
set -e

# Render provides $PORT (defaults to 10000 if running the image elsewhere).
: "${PORT:=10000}"

echo "==> Configuring Apache to listen on port ${PORT}"
echo "Listen ${PORT}" > /etc/apache2/ports.conf
sed -i "s/__PORT__/${PORT}/g" /etc/apache2/sites-available/000-default.conf

# Fail fast with a clear message if the app key wasn't provided.
if [ -z "${APP_KEY:-}" ]; then
  echo "WARNING: APP_KEY is not set. Generating an ephemeral key for this boot."
  echo "         Set a persistent APP_KEY env var in Render to keep sessions valid."
  export APP_KEY="base64:$(head -c 32 /dev/urandom | base64)"
fi

echo "==> Clearing stale caches"
php artisan optimize:clear || true

echo "==> Linking storage (for any locally-stored files)"
php artisan storage:link || true

echo "==> Running database migrations"
php artisan migrate --force

echo "==> Seeding demo accounts (idempotent)"
php artisan db:seed --force || true

echo "==> Caching config and views"
php artisan config:cache
php artisan view:cache
# NOTE: route:cache is intentionally skipped — some routes use closures,
# which Laravel cannot serialize. Config + view caching give the main win.

# The commands above ran as root; hand the writable dirs back to Apache's user
# so it can write logs, sessions and compiled views at runtime.
echo "==> Fixing storage permissions"
chown -R www-data:www-data storage bootstrap/cache

echo "==> Starting Apache"
exec apache2-foreground
