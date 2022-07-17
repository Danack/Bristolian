
set -e
set -x


ENV_TO_USE=${ENV_DESCRIPTION:=default}

echo "ENV_TO_USE is ${ENV_TO_USE}";


# Generate config settings used per environment
php vendor/bin/classconfig \
    -p config.source.php \
    "Bristolian\\Config" \
    config.generated.php \
    $ENV_TO_USE

# Generate nginx config file for the centos,dev environment
php vendor/bin/configurate \
    -p config.source.php \
    containers/php_fpm/config/fpm.conf.php \
    containers/php_fpm/config/fpm.conf \
    $ENV_TO_USE


# Generate nginx config file for the centos,dev environment
php vendor/bin/configurate \
    -p config.source.php \
    containers/php_fpm/config/php.ini.php \
    containers/php_fpm/config/php.ini \
    $ENV_TO_USE


/usr/sbin/php-fpm8.0 \
  --nodaemonize \
  --fpm-config=/var/app/containers/php_fpm/config/fpm.conf \
  -c /var/app/containers/php_fpm/config/php.ini