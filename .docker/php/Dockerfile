# FROM php:8.2-fpm

# WORKDIR /var/www/src

# RUN docker-php-ext-install pdo_mysql

# CMD ["php-fpm"]

FROM php:8.2-fpm

WORKDIR /var/www

RUN docker-php-ext-install pdo_mysql

# Копируйте ваш php.ini в контейнер
COPY .docker/php/php.ini /usr/local/etc/php/php.ini

CMD ["php-fpm"]
