FROM php:7.2-fpm

# Some libs
RUN apt-get update && apt-get install -y --no-install-recommends vim curl locales apt-utils

# https://github.com/mlocati/docker-php-extension-installer
ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions /usr/local/bin/

RUN chmod uga+x /usr/local/bin/install-php-extensions && sync && install-php-extensions \
    apcu opcache intl \
    gd imagick \
    pdo_mysql \
    mbstring \
    xdebug \
    zip

# PHP Conf
COPY php.ini /usr/local/etc/php/php.ini
COPY php-fpm-pool.conf /usr/local/etc/php/php-fpm.conf

# Install composer
RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
	mv composer.phar /usr/local/bin/composer

RUN usermod -u 1000 www-data

# Purge
RUN rm -rf /var/lib/apt/lists/* \
    && apt-get purge --auto-remove -y g++ \
    && apt-get clean

WORKDIR /var/www/
USER www-data

EXPOSE 9000
CMD ["php-fpm"]
