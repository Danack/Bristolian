#!/usr/bin/env bash


if test -f "./this_is_prod.txt"; then
    echo "this_is_prod.txt exists, delete that if you want to run prod."
    exit -1
fi

touch this_is_local.txt

docker-compose up --build -d db

docker-compose up --build installer

docker-compose build php_fpm

docker-compose up --build \
 varnish caddy \
 php_fpm php_fpm_debug \
 sass_dev_builder \
 js_builder \
 redis


# chrome_headless \

# docker-compose up --build varnish nginx php_fpm php_fpm_debug npm npm_dev_build redis
# docker-compose up --build php_fpm npm_dev_build


#docker-compose up --build db varnish caddy php_fpm php_fpm_debug