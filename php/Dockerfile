FROM php:7.2-apache
COPY php.ini /usr/local/etc/php/
RUN apt-get update \
  && docker-php-ext-install pdo_mysql mysqli