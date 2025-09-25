FROM php:8.3-fpm-alpine

ARG USER_ID=1000
ARG GROUP_ID=1000

RUN apk update && apk add --no-cache \
    build-base \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    zip \
    jpegoptim optipng pngquant gifsicle \
    unzip \
    oniguruma-dev \
    curl \
    postgresql-dev \
    && docker-php-ext-install pdo_mysql pdo_pgsql mbstring zip exif pcntl \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install gd \
    && apk add --no-cache linux-headers \
    && apk add --update --no-cache --virtual .build-dependencies $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && pecl clear-cache \
    && apk del .build-dependencies

RUN set -eux; \
  persistentDeps=" \
    c-client \
  "; \
  buildDeps=" \
    imap-dev \
    krb5-dev \
    openssl-dev \
  "; \
  apk add --no-cache --virtual .imap-persistent-deps ${persistentDeps}; \
  apk add --no-cache --virtual .imap-build-deps ${buildDeps}; \
  docker-php-ext-configure imap \
    --with-imap-ssl \
    --with-kerberos \
  ; \
  docker-php-ext-install -j$(nproc) imap; \
  apk del --no-cache --no-network .imap-build-deps

RUN set -xe \
    && apk add --update \
        icu \
    && apk add --no-cache --virtual .php-deps \
        make \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        zlib-dev \
        icu-dev \
        g++ \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-enable intl \
    && { find /usr/local/lib -type f -print0 | xargs -0r strip --strip-all -p 2>/dev/null || true; } \
    && apk del .build-deps \
    && rm -rf /tmp/* /usr/local/lib/php/doc/* /var/cache/apk/*

RUN addgroup -g $GROUP_ID app && adduser -u $USER_ID -G app -s /bin/sh -D app

RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

COPY ./conf.d $PHP_INI_DIR/conf.d
WORKDIR /app

USER app
