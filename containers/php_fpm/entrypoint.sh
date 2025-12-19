
set -e
set -x

# tail -f /var/app/README.md

exec /usr/sbin/php-fpm8.2 \
  --nodaemonize \
  --fpm-config=/var/app/containers/php_fpm/config/fpm.conf \
  -c /var/app/containers/php_fpm/config/php.ini