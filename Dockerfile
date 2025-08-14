FROM php:8.4-cli

# Instalar dependências do sistema e extensões do PHP
RUN apt-get update && apt-get install -y \
    git unzip pkg-config libzip-dev libpng-dev libjpeg-dev libonig-dev libxml2-dev \
    libssl-dev libcurl4-openssl-dev libbrotli-dev \
    && docker-php-ext-install zip pdo pdo_mysql gd bcmath \
    && pecl install swoole \
    && docker-php-ext-enable swoole \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pcntl

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configuração PHP customizada
COPY .docker/php.ini /usr/local/etc/php/php.ini

WORKDIR /var/www

COPY .docker/install.sh /usr/local/bin/install.sh
RUN chmod +x /usr/local/bin/install.sh

CMD ["install.sh"]
