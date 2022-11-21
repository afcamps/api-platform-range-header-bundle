FROM composer:2.4.4 AS composer

FROM php:8.1-apache-buster

COPY --from=composer /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER 1

RUN apt-get update && apt-get install -y \
  git \
  libzip-dev \
  && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql zip

RUN a2enmod rewrite
