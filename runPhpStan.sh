#!/usr/bin/env bash

set -e

# docker-compose exec -T php_fpm sh -c "php vendor/bin/phpstan analyze -c ./phpstan.neon -l 7 lib"

# --no-progress
php vendor/bin/phpstan analyze -vvv -c ./phpstan.neon --error-format=llm "$@"