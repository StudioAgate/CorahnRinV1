FROM composer

FROM php:7.3-fpm-stretch

LABEL maintainer="pierstoval@gmail.com"

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY etc/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY --from=composer /usr/bin/composer /usr/local/bin/composer

RUN apt-get update \
    && apt-get install -y \
        libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
        libzip-dev zip unzip \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-configure pdo_mysql \
    && docker-php-ext-configure zip \
    && docker-php-ext-install opcache pdo_mysql zip \
    && apt-get clean \
    && addgroup foo \
    && adduser --gecos "" --disabled-password --home=/usr/share/nginx/html --shell=/bin/sh --ingroup foo foo

USER foo

WORKDIR /usr/share/nginx/html
