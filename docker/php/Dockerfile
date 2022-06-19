FROM php:7.4-apache

RUN apt-get update\
    && apt-get install -y git unzip cron\
    && pecl install xdebug\
    && pecl install redis\
    && docker-php-ext-enable xdebug\
    && docker-php-ext-enable redis\
    && docker-php-ext-install pdo_mysql\
    && docker-php-ext-install mysqli\
    && a2enmod rewrite

COPY ./000-default.conf /etc/apache2/sites-available/
COPY ./php.ini          /usr/local/etc/php/
COPY ./startup.sh       /

CMD /startup.sh
