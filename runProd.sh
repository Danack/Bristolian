#!/usr/bin/env bash

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
NGINX_CONF_DIR="$SCRIPT_DIR/containers/caddy"
NGINX_SITE_LINK="$NGINX_CONF_DIR/bristolian.org.conf"

use_nginx_config() {
    local config_name="$1"
    ln -sfn "$config_name" "$NGINX_SITE_LINK"
    sudo /usr/sbin/nginx -t
    sudo /usr/sbin/nginx -s reload
    echo "Nginx is using $config_name."
}

if test -f "./this_is_local.txt"; then
    echo "this_is_local.txt exists, delete that if you want to run prod."
    exit -1
fi

touch this_is_prod.txt

use_nginx_config "bristolian.org.maintenance.conf"
set -e

docker-compose up --build -d db

docker-compose -f docker-compose.yml -f docker-compose.prod.yml up  --build --force-recreate installer
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up  --build --force-recreate js_and_css_prod_builder

docker-compose -f docker-compose.yml -f docker-compose.prod.yml up  --build --force-recreate -d redis
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up  --build --force-recreate -d php_fpm
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up  --build --force-recreate -d supervisord
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up  --build --force-recreate -d caddy
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up  --build --force-recreate -d varnish

use_nginx_config "bristolian.org.up.conf"

