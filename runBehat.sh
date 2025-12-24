#!/usr/bin/env bash

set -e

php vendor/bin/behat --config=./behat.yml --colors --stop-on-failure features/basic.feature
# php vendor/bin/behat --config=./behat.yml --colors --stop-on-failure features/bristol_stairs.feature --name 'Upload stair image with 8 steps'