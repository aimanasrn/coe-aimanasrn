#!/bin/sh
set -eu

if [ ! -f .env ]; then
    cp .env.example .env
fi

composer install --no-interaction --prefer-dist --no-progress
php artisan config:clear --no-interaction

if [ -z "${APP_KEY:-}" ]; then
    unset APP_KEY
    if ! grep -Eq '^APP_KEY=.+$' .env; then
        php artisan key:generate --force --no-interaction
    fi
fi

php artisan migrate --force --no-interaction

if [ "${OBJECT_STORAGE_INITIALIZE:-false}" = "true" ]; then
    php artisan storage:initialize-local --import-local --no-interaction
fi

exec "$@"
