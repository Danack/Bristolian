#!/usr/bin/env bash

set -euo pipefail

CONTAINER="bristolian-db-1"

## Prompt for MySQL password securely
#read -s -p "Enter MySQL password: " MYSQL_PASSWORD
#echo

MYSQL_PASSWORD="PrJaGpnNSzSLW8p8"

docker exec -i "$CONTAINER" mysql -u root -p"$MYSQL_PASSWORD" -e "SET GLOBAL max_connections = 2000;"