FROM bristolian_php_fpm:latest

RUN DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
  php8.2-xdebug valgrind strace

RUN rm /etc/php/8.2/fpm/conf.d/20-xdebug.ini
RUN ln -s /var/app/containers/php_fpm_debug/xdebug.ini /etc/php/8.2/fpm/conf.d/20-xdebug.ini

RUN rm /etc/php/8.2/cli/conf.d/20-xdebug.ini
RUN ln -s /var/app/containers/php_fpm_debug/xdebug.ini /etc/php/8.2/cli/conf.d/20-xdebug.ini

COPY valgrind.sh /usr/local/sbin/valgrind
RUN chmod +x /usr/local/sbin/valgrind
