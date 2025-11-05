FROM php:8.4-apache

# ========================================
# 1️⃣ Instala dependências do sistema
# ========================================
RUN apt-get update && apt-get install -y \
    git curl zip unzip npm nodejs tzdata \
    libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev libicu-dev libmagickwand-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install sockets pdo pdo_mysql mbstring zip exif pcntl bcmath gd intl \
    # Instala imagick a partir do código-fonte
    && git clone https://github.com/Imagick/imagick.git /usr/src/php/ext/imagick \
    && cd /usr/src/php/ext/imagick && phpize && ./configure && make -j$(nproc) && make install \
    && docker-php-ext-enable imagick sockets bcmath zip intl \
    && rm -rf /var/lib/apt/lists/* && apt-get clean

# ========================================
# 2️⃣ Configurações do PHP
# ========================================
RUN { \
    echo "memory_limit=512M"; \
    echo "upload_max_filesize=64M"; \
    echo "post_max_size=64M"; \
    echo "max_execution_time=300"; \
} > /usr/local/etc/php/conf.d/custom-php.ini

# Fuso horário
RUN ln -snf /usr/share/zoneinfo/America/Sao_Paulo /etc/localtime && echo "America/Sao_Paulo" > /etc/timezone

# ========================================
# 3️⃣ Apache e Composer
# ========================================
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
RUN a2enmod rewrite \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# ========================================
# 4️⃣ Copia os arquivos e define diretório
# ========================================
WORKDIR /var/www/html
COPY . .

# ========================================
# 5️⃣ Instala dependências PHP (como root)
# ========================================
ENV COMPOSER_MEMORY_LIMIT=-1
ENV COMPOSER_PROCESS_TIMEOUT=1800

# Corrige permissões antes do composer
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html

# ⚠️ Aqui mantemos root — composer cria o vendor/ sem erro
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# ========================================
# 6️⃣ Instala dependências JS (como root)
# ========================================
RUN npm install

# ========================================
# 7️⃣ Prepara diretórios Laravel e permissões
# ========================================
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# ========================================
# 8️⃣ Define usuário e expõe porta
# ========================================
USER www-data
EXPOSE 80

# ========================================
# 9️⃣ Comando padrão
# ========================================
CMD ["apache2-foreground"]
