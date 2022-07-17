FROM bristolian_php_fpm:latest

RUN DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
  php8.0-xdebug valgrind strace

RUN rm /etc/php/8.0/fpm/conf.d/20-xdebug.ini
RUN ln -s /var/app/containers/php_fpm_debug/xdebug.ini /etc/php/8.0/fpm/conf.d/20-xdebug.ini

RUN rm /etc/php/8.0/cli/conf.d/20-xdebug.ini
RUN ln -s /var/app/containers/php_fpm_debug/xdebug.ini /etc/php/8.0/cli/conf.d/20-xdebug.ini

COPY valgrind.sh /usr/local/sbin/valgrind
RUN chmod +x /usr/local/sbin/valgrind
