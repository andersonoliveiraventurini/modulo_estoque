FROM php:8.4-apache

# ===============================
# PHP Configuração Base
# ===============================
COPY docker/php.ini /usr/local/etc/php/conf.d/00-base.ini

# ===============================
# Dependências do sistema
# ===============================
RUN apt-get update && apt-get install -y \
    git curl zip unzip tzdata \
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
    && pecl install imagick \
    && docker-php-ext-enable imagick sockets bcmath zip intl \
    && rm -rf /var/lib/apt/lists/*

# ===============================
# Node 20 LTS
# ===============================
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# ===============================
# Timezone
# ===============================
RUN ln -snf /usr/share/zoneinfo/America/Sao_Paulo /etc/localtime \
    && echo "America/Sao_Paulo" > /etc/timezone

# ===============================
# Apache
# ===============================
RUN a2enmod rewrite \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' \
       /etc/apache2/sites-available/000-default.conf

# ===============================
# Composer
# ===============================
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

ENV COMPOSER_MEMORY_LIMIT=-1
ENV COMPOSER_PROCESS_TIMEOUT=1800

# ===============================
# EntryPoint
# ===============================
# COPY docker/entrypoint.sh /entrypoint.sh
# RUN chmod +x /entrypoint.sh

# ENTRYPOINT ["/entrypoint.sh"]

EXPOSE 80
CMD ["apache2-foreground"]