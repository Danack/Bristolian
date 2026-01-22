#!/usr/bin/env bash

# php vendor/bin/phpunit -c phpunit.xml --exclude slow "$@"
php vendor/bin/phpunit -c phpunit.xml "$@"
