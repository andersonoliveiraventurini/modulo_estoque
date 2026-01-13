FROM php:8.4-apache

# ========================================
# 1️⃣ Dependências do sistema e extensões PHP
# ========================================
RUN apt-get update && apt-get install -y \
    git curl zip unzip npm nodejs tzdata \
    libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev libicu-dev libmagickwand-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        sockets \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
    && git clone https://github.com/Imagick/imagick.git /usr/src/php/ext/imagick \
    && cd /usr/src/php/ext/imagick \
    && phpize \
    && ./configure \
    && make -j$(nproc) \
    && make install \
    && docker-php-ext-enable imagick sockets bcmath zip intl \
    && rm -rf /var/lib/apt/lists/* \
    && apt-get clean

# ========================================
# 2️⃣ Configurações do PHP (produção)
# ========================================
RUN { \
    echo "memory_limit=512M"; \
    echo "upload_max_filesize=64M"; \
    echo "post_max_size=64M"; \
    echo "max_execution_time=300"; \
    echo "opcache.enable=1"; \
    echo "opcache.memory_consumption=256"; \
    echo "opcache.max_accelerated_files=20000"; \
    echo "opcache.validate_timestamps=0"; \
    } > /usr/local/etc/php/conf.d/custom-php.ini

RUN ln -snf /usr/share/zoneinfo/America/Sao_Paulo /etc/localtime \
    && echo "America/Sao_Paulo" > /etc/timezone

# ========================================
# 3️⃣ Apache e Composer
# ========================================
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN a2enmod rewrite \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' \
       /etc/apache2/sites-available/000-default.conf

# ========================================
# 4️⃣ Diretório de trabalho
# ========================================
WORKDIR /var/www/html

# ========================================
# 5️⃣ Composer (cache-friendly)
# ========================================
COPY composer.json composer.lock ./

ENV COMPOSER_MEMORY_LIMIT=-1
ENV COMPOSER_PROCESS_TIMEOUT=1800

RUN composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-dev \
    --no-scripts

# ========================================
# 6️⃣ Código da aplicação
# ========================================
COPY . .

# ========================================
# 7️⃣ Dependências JS (produção)
# ========================================
RUN npm install --omit=dev

# ========================================
# 8️⃣ Diretórios Laravel
# ========================================
RUN mkdir -p \
    storage/app/public \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# ========================================
# 9️⃣ Composer autoload APÓS criar diretórios
# ========================================
RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi

# ========================================
# 🔟 EntryPoint
# ========================================
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]

# ========================================
# 1️⃣1️⃣ Porta e comando padrão
# ========================================
EXPOSE 80
CMD ["apache2-foreground"]