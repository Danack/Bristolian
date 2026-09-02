#!/usr/bin/env bash

# php vendor/bin/phpunit -c phpunit.xml --exclude slow "$@"



php vendor/bin/phpunit -c phpunit.xml "$@"


# php vendor/bin/paratest -c phpunit.xml --processes=auto "$@"


# php vendor/bin/paratest -c phpunit.xml --processes=auto --no-progress --no-coverage
