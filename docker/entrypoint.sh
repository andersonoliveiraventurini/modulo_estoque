#!/bin/bash
set -e

echo "🚀 Iniciando Laravel Container"
echo "🔍 Ambiente: ${APP_ENV}"

# ===============================
# Diretórios Laravel
# ===============================
mkdir -p storage/app/public
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# ===============================
# Storage link
# ===============================
if [ ! -L public/storage ]; then
    php artisan storage:link || true
fi

echo "✅ Setup concluído. Iniciando Apache..."

exec "$@"
