
set -e
set -x


ENV_TO_USE=${ENV_DESCRIPTION:=default}

echo "ENV_TO_USE is ${ENV_TO_USE}";
git config --global --add safe.directory /var/app

if test -f "./composer_oauth_token.txt"; then
    echo "Composer oauth token appears to exist. Trying to use it."
    set +x
    oauthtoken=`cat ./composer_oauth_token.txt`
    php composer.phar config -g github-oauth.github.com $oauthtoken
    set -x
fi

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
    "Bristolian\\Config\\Config" \
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
# to be available prevents annoyance.
php cli.php db:wait_for_db

php cli.php db:migrate_to_latest

php cli.php generate:javascript_constants
php cli.php generate:php_table_helper_classes

cd /var/app/chat

if [ "${COMPOSER_TYPE}" = "update" ]; then
    php ../composer.phar update
else
    php ../composer.phar install
fi

echo "Installer is finished."