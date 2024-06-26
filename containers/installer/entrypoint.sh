
set -e
set -x


ENV_TO_USE=${ENV_DESCRIPTION:=default}

echo "ENV_TO_USE is ${ENV_TO_USE}";
git config --global --add safe.directory /var/app

COMPOSER_TYPE=$(php src/check_composer_command.php)
echo "composer type is ${COMPOSER_TYPE}";
if [ "${COMPOSER_TYPE}" = "update" ]; then
    php composer.phar update
else
    php composer.phar install
fi

# tail -f /var/app/README.md

# Generate config settings used per environment
php vendor/bin/classconfig \
    -p config.source.php \
    "Bristolian\\Config" \
    config.generated.php \
    $ENV_TO_USE

# Generate fpm config file
php vendor/bin/configurate \
    -p config.source.php \
    containers/php_fpm/config/fpm.conf.php \
    containers/php_fpm/config/fpm.conf \
    $ENV_TO_USE

# Generate varnish config
php vendor/bin/configurate \
    -p config.source.php \
    containers/varnish/config/default.vcl.php \
    containers/varnish/config/default.vcl \
    $ENV_TO_USE

# There can be a race condition between the DB coming
# up, and us trying to use it. Explicitly waiting for it
# to be available save annoyance.
php cli.php db:wait_for_db

php cli.php db:migrate_to_latest


echo "Installer is finished."