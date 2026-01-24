#!/usr/bin/env bash

set -e

# docker-compose exec -T php_fpm sh -c "php phpstan.phar analyze -c ./phpstan.neon -l 7 lib"

# --no-progress
php phpstan.phar analyze -vvv -c ./phpstan.neon "$@"