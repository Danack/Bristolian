
set -e
set -x


ENV_TO_USE=${ENV_DESCRIPTION:=default}

echo "ENV_TO_USE is ${ENV_TO_USE}";


COMPOSER_TYPE=$(php src/check_composer_command.php)
echo "composer type is ${COMPOSER_TYPE}";
if [ "${COMPOSER_TYPE}" = "update" ]; then
    php composer.phar update
else
    php composer.phar install
fi


# Generate config settings used per environment
php vendor/bin/classconfig \
    -p config.source.php \
    "Bristolian\\Config" \
    config.generated.php \
    $ENV_TO_USE

# There can be a race condition between the DB coming
# up, and us trying to use it. Explicitly waiting for it
# to be available save annoyance.
php cli.php db:wait_for_db

php cli.php db:migrate_to_latest

php vendor/bin/phinx migrate -e development
# php cli.php seed:initial

echo "Installer is finished."