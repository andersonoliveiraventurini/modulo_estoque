FROM php:8.4-apache

# Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libmagickwand-dev \
    npm \
    nodejs \
    tzdata \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install sockets pdo pdo_mysql mbstring zip exif pcntl bcmath gd intl \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && docker-php-ext-enable sockets bcmath zip intl \
    && rm -rf /var/lib/apt/lists/* \
    && apt-get clean

# Aumenta limite de memória do PHP
RUN { \
    echo "memory_limit=512M"; \
    echo "upload_max_filesize=64M"; \
    echo "post_max_size=64M"; \
} > /usr/local/etc/php/conf.d/custom-php.ini

# Ajusta o fuso horário para o Brasil
RUN ln -snf /usr/share/zoneinfo/America/Sao_Paulo /etc/localtime && echo "America/Sao_Paulo" > /etc/timezone

# Copia o Composer da imagem oficial
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Ativa o mod_rewrite do Apache
RUN a2enmod rewrite

# Define o ServerName para evitar erro AH00558
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Altera o DocumentRoot para a pasta public do Laravel
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia os arquivos do projeto
COPY . .

# Instala dependências PHP do Laravel
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Instala dependências JS
RUN npm install

# Cria diretórios necessários e ajusta permissões
RUN mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/framework/cache \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Expõe a porta padrão do Apache
EXPOSE 80

# Comando padrão do container
CMD ["apache2-foreground"]