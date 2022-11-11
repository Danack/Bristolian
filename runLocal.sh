#!/usr/bin/env bash

docker-compose up --build -d db

docker-compose up --build installer

docker-compose up --build \
 varnish caddy \
 php_fpm php_fpm_debug \
 sass_dev_builder \
 js_builder \
 redis


# chrome_headless \

# docker-compose up --build varnish nginx php_fpm php_fpm_debug npm npm_dev_build redis
# docker-compose up --build php_fpm npm_dev_build


docker-compose up --build db varnish nginx php_fpm php_fpm_debug