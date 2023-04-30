#!/usr/bin/env bash


# docker-compose build

docker-compose -f docker-compose.yml -f docker-compose.prod.yml up  --build --force-recreate installer
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up  --build --force-recreate npm_build

docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build --force-recreate varnish caddy php_fpm redis


# docker-compose -f docker-compose.yml -f docker-compose.prod.yml up --build --force-recreate varnish nginx php_fpm
