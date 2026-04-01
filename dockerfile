FROM php:8.2-apache

RUN apt-get update && apt-get install -y libssl-dev \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

RUN a2enmod rewrite

COPY . /var/www/html/

COPY .htaccess /var/www/html/.htaccess

EXPOSE 80