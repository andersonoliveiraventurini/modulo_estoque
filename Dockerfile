FROM php:8.4-apache

# ========================================
# 1️⃣ Instala dependências do sistema
# ========================================
# (Nenhuma alteração aqui, está ótimo)
RUN apt-get update && apt-get install -y \
    git curl zip unzip npm nodejs tzdata \
    libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev libicu-dev libmagickwand-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install sockets pdo pdo_mysql mbstring zip exif pcntl bcmath gd intl \
    && git clone https://github.com/Imagick/imagick.git /usr/src/php/ext/imagick \
    && cd /usr/src/php/ext/imagick && phpize && ./configure && make -j$(nproc ) && make install \
    && docker-php-ext-enable imagick sockets bcmath zip intl \
    && rm -rf /var/lib/apt/lists/* && apt-get clean

# ========================================
# 2️⃣ Configurações do PHP
# ========================================
# (Nenhuma alteração aqui)
RUN { \
    echo "memory_limit=512M"; \
    echo "upload_max_filesize=64M"; \
    echo "post_max_size=64M"; \
    echo "max_execution_time=300"; \
} > /usr/local/etc/php/conf.d/custom-php.ini
RUN ln -snf /usr/share/zoneinfo/America/Sao_Paulo /etc/localtime && echo "America/Sao_Paulo" > /etc/timezone

# ========================================
# 3️⃣ Apache e Composer
# ========================================
# (Nenhuma alteração aqui)
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
RUN a2enmod rewrite \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# ========================================
# 4️⃣ Define diretório e instala dependências PHP (Otimizado)
# ========================================
WORKDIR /var/www/html

# NOVO: Copia apenas os arquivos do Composer primeiro para otimizar o cache
COPY composer.json composer.lock ./

# NOVO: Instala dependências do Composer como root
ENV COMPOSER_MEMORY_LIMIT=-1
ENV COMPOSER_PROCESS_TIMEOUT=1800
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev --no-scripts

# ========================================
# 5️⃣ Copia os arquivos do projeto
# ========================================
# NOVO: Agora copia o resto dos arquivos do projeto
COPY . .

# ========================================
# 6️⃣ Instala dependências JS (como root)
# ========================================
# (Nenhuma alteração aqui)
RUN npm install

# ========================================
# 7️⃣ Prepara diretórios Laravel e permissões
# ========================================

RUN mkdir -p storage/app/public \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Agora sim pode rodar artisan e composer scripts
RUN composer dump-autoload --optimize \
    && php artisan storage:link


# ========================================
# 8️⃣ Define usuário e expõe porta
# ========================================
# (Nenhuma alteração aqui)
USER www-data
EXPOSE 80

# ========================================
# 9️⃣ Comando padrão
# ========================================
# (Nenhuma alteração aqui)
CMD ["apache2-foreground"]