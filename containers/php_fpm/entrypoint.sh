
set -e
set -x

# tail -f /var/app/README.md

/usr/sbin/php-fpm8.1 \
  --nodaemonize \
  --fpm-config=/var/app/containers/php_fpm/config/fpm.conf \
  -c /var/app/containers/php_fpm/config/php.ini