FROM php:7.2.10-fpm

##<romaricp>##
RUN apt-get update \
    && apt-get install -y --no-install-recommends vim curl debconf subversion git apt-transport-https apt-utils \
    build-essential locales acl mailutils wget nodejs zip unzip \
    zlib1g-dev libicu-dev g++ \
    gnupg gnupg1 gnupg2

# PHP Extensions
RUN docker-php-ext-configure intl && docker-php-ext-install intl
RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-install intl
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install zip

# install GD
RUN apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
&& docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
&& docker-php-ext-install gd

# Install Image Extensions: Imagemagick
RUN apt-get install -y libmagickwand-dev --no-install-recommends \
&& pecl install imagick && docker-php-ext-enable imagick

# install apcu
RUN pecl install apcu && docker-php-ext-enable apcu

# Install Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug \
&& echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
&& echo "xdebug.remote_enable=1" >> /usr/local/etc/php/conf.d/xdebug.ini \
&& echo "xdebug.remote_autostart=1" >> /usr/local/etc/php/conf.d/xdebug.ini \
&& echo "xdebug.default_enable=1" >> /usr/local/etc/php/conf.d/xdebug.ini \
&& echo "xdebug.remote_connect_back=0" >> /usr/local/etc/php/conf.d/xdebug.ini \
&& echo "xdebug.remote_handler=\"dbgp\"" >> /usr/local/etc/php/conf.d/xdebug.ini \
&& echo "xdebug.remote_host=localhost" >> /usr/local/etc/php/conf.d/xdebug.ini \
&& echo "xdebug.remote_port=9010" >> /usr/local/etc/php/conf.d/xdebug.ini
&& echo "xdebug.idekey=PHPSTORM" >> /usr/local/etc/php/conf.d/xdebug.ini


# PHP Conf
COPY php.ini /etc/php/7.2.10/php.ini
COPY php-fpm-pool.conf /etc/php/7.2.10/pool.d/www.conf

# Install composer
RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
	mv composer.phar /usr/local/bin/composer

RUN groupadd dev -g 999
RUN useradd dev -g dev -d /home/dev -m

RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen && \
    echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen && \
    locale-gen

# Purge
RUN rm -rf /var/lib/apt/lists/* \
    && apt-get purge --auto-remove -y g++ \
    && apt-get clean

WORKDIR /var/www/
##</romaricp>##

EXPOSE 9000
CMD ["php-fpm"]
