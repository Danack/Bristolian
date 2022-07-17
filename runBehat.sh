#!/usr/bin/env bash

set -e

# docker-compose exec -T php_fpm sh -c "php vendor/bin/behat --config=./behat.yml --colors --stop-on-failure"

php vendor/bin/behat --config=./behat.yml --colors --stop-on-failure