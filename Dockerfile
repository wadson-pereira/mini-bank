FROM composer:2.6.6 as composer

WORKDIR /app
COPY composer.json composer.lock /app/
RUN composer install --no-scripts --no-autoloader --ignore-platform-reqs

FROM php:8.1-fpm

WORKDIR /app

RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    libpq-dev \
    && docker-php-ext-install zip pdo_mysql mysqli


RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer /app/vendor /app/vendor

COPY . /app/

RUN cp .env.example .env

RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

RUN chmod 777 -R /app/storage

EXPOSE 9000

CMD ["php-fpm"]
