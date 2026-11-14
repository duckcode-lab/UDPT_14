#!/bin/sh
set -eu

mkdir -p storage/framework storage/logs storage/app/public

if [ -z "${APP_KEY:-}" ]; then
    if [ -s storage/framework/docker-app-key ]; then
        APP_KEY="$(cat storage/framework/docker-app-key)"
    else
        APP_KEY="$(php artisan key:generate --show)"
        printf '%s\n' "$APP_KEY" > storage/framework/docker-app-key
    fi
    export APP_KEY
fi

php artisan storage:link --force

exec php artisan serve --host=0.0.0.0 --port=8000
