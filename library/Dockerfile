FROM nginx:1.20-alpine as nginx
FROM php:8-cli-alpine as php
FROM php:8-fpm-alpine as php-fpm
FROM mlocati/php-extension-installer as php-ext-installer
FROM composer:2 as composer
FROM migrate/migrate as migrate

FROM php-fpm as base-php-fpm
COPY --from=php-ext-installer /usr/bin/install-php-extensions /usr/bin/php-ext-install
COPY --from=migrate /usr/bin/migrate /usr/bin/migrate
COPY ./docker/php/10-*.ini /usr/local/etc/php/conf.d/
RUN php-ext-install pdo_pgsql sockets opcache
WORKDIR /opt/library/app

FROM nginx as base-nginx
RUN rm -rf /etc/nginx/conf.d/* && \
    mkdir -p /opt/library/app/public

FROM base-nginx as dev-nginx
COPY ./docker/nginx/nginx.dev.conf /etc/nginx/nginx.conf

FROM base-php-fpm as dev-php-fpm
ARG uid
ARG gid
COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY ./docker/php/90-*.ini /usr/local/etc/php/conf.d/
RUN addgroup -S -g ${gid} host && adduser -S -D -u ${uid} -G host host && \
    php-ext-install xdebug && \
    apk add --update --no-cache git
VOLUME ["/opt/library"]
