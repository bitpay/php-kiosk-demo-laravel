#!/bin/bash

if [ ! -e .env ] || [ ! -e application.yaml ]; then
    echo "Please read README.md and follow running instructions"
    exit 1
fi

if [ ! -d vendor ]; then
    composer install --no-progress --ignore-platform-reqs
fi

if [ ! -e database/*.sqlite ]; then
    touch database/bitpay.sqlite
    php artisan migrate --force
    php artisan key:generate --no-interaction
fi
