ARG PHP_VERSION=8.5
FROM php:${PHP_VERSION}-cli-bookworm

ENV XDEBUG_MODE=off

# Use the security updates available for the selected Debian base image.
# hadolint ignore=DL3008
RUN apt-get update && apt-get install -y --no-install-recommends \
      git \
      unzip \
    && rm -rf /var/lib/apt/lists/*

RUN pecl install xdebug-3.5.3 && docker-php-ext-enable xdebug

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /app

COPY . .
