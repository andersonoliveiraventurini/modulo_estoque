#!/bin/bash
set -e

echo "🚀 Iniciando Laravel Container"
echo "🔍 Ambiente: ${APP_ENV}"

# Diretórios Laravel
mkdir -p storage/app/public
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Storage link
if [ ! -L public/storage ]; then
    php artisan storage:link || true
fi

# Instala dependências PHP apenas se necessário
if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "📦 Instalando dependências PHP..."
    composer install --no-dev --optimize-autoloader
else
    echo "✅ Dependências PHP já instaladas."
fi

# Builda assets apenas se necessário
if [ ! -f /var/www/html/public/build/manifest.json ]; then
    echo "🎨 Buildando assets frontend..."
    npm install
    npm run build
else
    echo "✅ Assets já buildados."
fi

php artisan optimize

echo "✅ Setup concluído. Iniciando Apache..."
exec "$@"