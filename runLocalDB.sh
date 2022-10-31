#!/usr/bin/env bash

# docker-compose up --build installer

#docker-compose up --build \
# varnish nginx \
# php_fpm php_fpm_debug \
# chrome_headless \
# sass_dev_builder \
# js_dev_builder \
# redis


docker-compose up --build \
 db \
 php_fpm php_fpm_debug \


