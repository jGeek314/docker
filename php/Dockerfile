FROM php:7.2-apache
COPY php.ini /usr/local/etc/php/
RUN echo "ServerName web" >> /etc/apache2/apache2.conf
RUN apt-get update \
  && docker-php-ext-install pdo_mysql mysqli