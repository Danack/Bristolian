#!/usr/bin/env bash

# php vendor/bin/phpunit -c test/phpunit.xml --exclude slow "$@"
php vendor/bin/phpunit -c test/phpunit.xml "$@"
