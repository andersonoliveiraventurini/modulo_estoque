#!/bin/bash
set -e

echo "🚀 Iniciando Laravel Container..."

# Criar estrutura de diretórios se não existir
echo "📁 Verificando estrutura de diretórios..."
mkdir -p /var/www/html/storage/app/public
mkdir -p /var/www/html/storage/framework/{sessions,views,cache,testing}
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

# Ajustar permissões
echo "🔧 Ajustando permissões..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Criar symlink do storage se não existir
if [ ! -L /var/www/html/public/storage ]; then
    echo "🔗 Criando symlink do storage..."
    php artisan storage:link 2>/dev/null || echo "⚠️  Symlink já existe ou falhou"
fi

# Limpar caches se necessário (opcional)
if [ "${CLEAR_CACHE_ON_START}" = "true" ]; then
    echo "🧹 Limpando caches..."
    php artisan config:clear || true
    php artisan route:clear || true
    php artisan view:clear || true
fi

# Cache de configurações em produção
if [ "${APP_ENV}" = "production" ]; then
    echo "⚡ Otimizando para produção..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

echo "✅ Container pronto!"

# Executar o comando principal (Apache)
exec "$@"