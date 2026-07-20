#!/bin/sh
set -e

DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-5432}"
DB_USERNAME="${DB_USERNAME:-skillswap}"

echo "Waiting for PostgreSQL at ${DB_HOST}:${DB_PORT}..."
until pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" >/dev/null 2>&1; do
  sleep 1
done
echo "PostgreSQL is ready."

if [ -z "$APP_KEY" ]; then
  APP_KEY="$(php -r 'echo "base64:".base64_encode(random_bytes(32));')"
  export APP_KEY
  echo "Generated ephemeral APP_KEY for this container run."
fi

php artisan config:clear
php artisan migrate:fresh --seed --force

exec php artisan serve --host=0.0.0.0 --port=8000
