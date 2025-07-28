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
    npm \
    nodejs \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl gd \
    && apt-get clean

# Copia o Composer da imagem oficial
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Ativa mod_rewrite do Apache
RUN a2enmod rewrite

# Define o ServerName para evitar erro AH00558
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Altera o DocumentRoot para a pasta public do Laravel
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia todos os arquivos do projeto
COPY . .

# Instala dependências do Laravel
RUN composer install --no-interaction --prefer-dist --optimize-autoloader || cat /var/www/html/storage/logs/laravel.log || true

# Instala dependências JS e compila os assets
RUN npm install && npm run build

# Ajusta permissões
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]
