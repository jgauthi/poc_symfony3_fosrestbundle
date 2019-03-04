FROM php:7.2.15-fpm

# https://github.com/mlocati/docker-php-extension-installer
ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions /usr/local/bin/

RUN chmod uga+x /usr/local/bin/install-php-extensions && sync && install-php-extensions \
    apcu intl \
    gd imagick \
    pdo_mysql \
    mbstring \
    zip

# PHP Conf
COPY php.ini /etc/php/7.2.15/php.ini
COPY php-fpm-pool.conf /etc/php/7.2.15/pool.d/www.conf

# Install composer
RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
	mv composer.phar /usr/local/bin/composer

RUN usermod -u 1000 www-data

WORKDIR /var/www/

EXPOSE 9000
CMD ["php-fpm"]
