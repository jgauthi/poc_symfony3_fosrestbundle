FROM php:7.2.17-fpm

# Some libs
RUN apt-get update && apt-get install -y --no-install-recommends vim curl locales apt-utils

# https://github.com/mlocati/docker-php-extension-installer
ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions /usr/local/bin/

RUN chmod uga+x /usr/local/bin/install-php-extensions && sync && install-php-extensions \
    apcu intl \
    gd imagick \
    pdo_mysql \
    mbstring \
    xdebug \
    zip

# PHP Conf
COPY php.ini /usr/local/etc/php/php.ini
COPY php-fpm-pool.conf /usr/local/etc/php/php-fpm.conf

# PHP Xdebug conf
RUN printf "\n\
xdebug.remote_enable=On         ; Activer le debug distant\n\
xdebug.remote_connect_back=On   ; xDebug va automatiquement se connecter sur l'IP présente dans _SERVER['REMOTE_ADDR']\n\
xdebug.remote_host=localhost    ; Host à contacter si remote_connect_back est désactivé ou dans un contexte CLI\n\
xdebug.remote_port=9010         ; Port par défaut\n\
xdebug.idekey=\"dfk4G7_8bl1\"   ; Identifiant de session utilisé par l'IDE\n\
" >>  /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

#xdebug.remote_autostart=0
#xdebug.remote_connect_back=1
#xdebug.remote_cookie_expire_time = 3600
#xdebug.remote_enable = 1
#xdebug.remote_host = localhost
#xdebug.remote_port = 9000
#
#xdebug.remote_handler = dbgp

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

CMD ["php-fpm"]
