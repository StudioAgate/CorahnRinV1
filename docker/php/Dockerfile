FROM debian:10-slim

LABEL maintainer="pierstoval@gmail.com"

WORKDIR /srv

CMD ["symfony", "serve", "--dir=/srv", "--document-root=/srv/webroot", "--allow-http", "--port=8080"]

EXPOSE 8080

ENTRYPOINT ["/bin/entrypoint"]

ENV PHP_VERSION=7.4 \
    GOSU_VERSION=1.12 \
    PATH=/home/.composer/vendor/bin:$PATH \
    PATH=/home/.config/composer/vendor/bin:$PATH \
    RUN_USER="_www"

#COPY docker/php/etc/php-fpm.conf /usr/local/etc/php-fpm.conf
#COPY docker/php/etc/docker.conf /usr/local/etc/php-fpm.d/docker.conf
COPY docker/php/bin/entrypoint.sh /bin/entrypoint
COPY docker/php/etc/php.ini /etc/php/${PHP_VERSION}/fpm/conf.d/99-custom.ini
COPY docker/php/etc/php.ini /etc/php/${PHP_VERSION}/cli/conf.d/99-custom.ini

RUN set -xe \
    && apt-get update \
    && apt-get upgrade -y \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        git \
        curl \
        wget \
        unzip \
        dialog apt-utils `# Prevents having this issue: https://github.com/moby/moby/issues/27988` \
    \
    && `# Deb Sury PHP repository` \
    && apt-get -y install apt-transport-https lsb-release ca-certificates curl \
    && wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg \
    && sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list' \
    && apt-get update \
    \
    && `# PHP and extensions` \
    && apt-get install -y \
        php${PHP_VERSION} \
        php${PHP_VERSION}-cli \
        php${PHP_VERSION}-common \
        php${PHP_VERSION}-fpm \
        php${PHP_VERSION}-gd \
        php${PHP_VERSION}-mbstring \
        php${PHP_VERSION}-mysql \
        php${PHP_VERSION}-opcache \
        php${PHP_VERSION}-readline \
        php${PHP_VERSION}-xml \
        php${PHP_VERSION}-zip \
    \
    && `# User management for entrypoint` \
    && chmod +x /bin/entrypoint \
    && curl -L -s -o /bin/gosu https://github.com/tianon/gosu/releases/download/${GOSU_VERSION}/gosu-$(dpkg --print-architecture | awk -F- '{ print $NF }') \
    && chmod +x /bin/gosu \
    && mkdir -p /home \
    && groupadd ${RUN_USER} \
    && adduser --home=/home --shell=/bin/bash --ingroup=${RUN_USER} --disabled-password --quiet --gecos "" --force-badname ${RUN_USER} \
    && chown ${RUN_USER}:${RUN_USER} /home \
    \
    && `# Symfony CLI` \
    && (wget https://get.symfony.com/cli/installer -O - | bash) \
    && mv /root/.symfony/bin/symfony /usr/local/bin/symfony \
    && chown ${RUN_USER}:${RUN_USER} /usr/local/bin/symfony \
    && chmod a+x /usr/local/bin/symfony \
    \
    && `# Composer` \
    && (curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer) \
    \
    && `# Clean apt and remove unused libs/packages to make image smaller` \
    && runuser -l $RUN_USER -c 'composer clearcache' \
    && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false -o APT::AutoRemove::SuggestsImportant=false $BUILD_LIBS \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /var/www/* /var/cache/* /home/.composer/cache
