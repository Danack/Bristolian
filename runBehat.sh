#!/usr/bin/env bash

set -e

php vendor/bin/behat --config=./behat.yml --colors --stop-on-failure features/basic.feature