#!/usr/bin/env bash


# docker-compose build

docker-compose up --build installer
docker-compose up --build varnish nginx php_fpm php_fpm_debug

# docker-compose up --build varnish nginx php_fpm php_fpm_debug npm npm_dev_build redis
# docker-compose up --build php_fpm npm_dev_build
