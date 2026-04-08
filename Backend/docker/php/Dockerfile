FROM php:8.3-cli-alpine

# Dépendances système
RUN apk add --no-cache \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    icu-dev \
    oniguruma-dev \
    libzip-dev \
    openssl

# Extensions PHP
RUN docker-php-ext-install \
    pdo_mysql \
    intl \
    zip \
    opcache

# Redis extension
RUN apk add --no-cache autoconf g++ make \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del autoconf g++ make

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Génération des clés JWT au démarrage si absentes
COPY docker/php/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
