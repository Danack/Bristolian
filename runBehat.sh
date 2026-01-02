#!/usr/bin/env bash

set -e

FEATURE_PATH="${1:-features}"
php vendor/bin/behat --config=./behat.yml --colors --stop-on-failure "$FEATURE_PATH"