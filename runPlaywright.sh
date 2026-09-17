#!/usr/bin/env bash

set -e

docker exec bristolian-js_builder-1 bash -c "cd /var/app/app && npm run test:e2e -- \"\$@\"" -- "$@"
