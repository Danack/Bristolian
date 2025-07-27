#!/usr/bin/env bash


if test -f "./this_is_local.txt"; then
    echo "this_is_local.txt exists, delete that if you want to run prod."
    exit -1
fi

touch this_is_prod.txt

docker-compose up --build -d db

docker-compose -f docker-compose.yml -f docker-compose.prod.yml up  --build --force-recreate installer
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up  --build --force-recreate js_and_css_prod_builder

docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d \
 --build --force-recreate \
  caddy \
  php_fpm \
  redis \
  supervisord \
  varnish \
