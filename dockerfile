FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libssl-dev \
    unzip \
    ca-certificates \
    openssl \
    && update-ca-certificates \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

RUN a2enmod rewrite

RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# ✅ Composer install karo
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /var/www/html/

# ✅ Vendor dependencies install karo
RUN cd /var/www/html && composer install --no-dev --optimize-autoloader

EXPOSE 80