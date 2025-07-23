FROM php:8.4-apache

# Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql zip mbstring exif pcntl bcmath gd

# Habilita o módulo rewrite do Apache
RUN a2enmod rewrite

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia os arquivos do Laravel
COPY . .

# Instala as dependências do Laravel 12
RUN composer install --no-dev --prefer-dist --no-interaction

# Permissões corretas
RUN chmod -R 775 storage bootstrap/cache

# ajusta horário Brasil
RUN ln -snf /usr/share/zoneinfo/America/Sao_Paulo /etc/localtime && echo "America/Sao_Paulo" > /etc/timezone

# Comando padrão do container
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8010"]
