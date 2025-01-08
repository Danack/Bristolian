#!/usr/bin/env bash


if test -f "./this_is_prod.txt"; then
    echo "this_is_prod.txt exists, delete that if you want to run prod."
    exit -1
fi

touch this_is_local.txt

docker-compose up --build -d db

docker-compose up --build installer --exit-code-from installer

INSTALLER_EXIT_CODE=$?
echo "Installer exit code"
echo $INSTALLER_EXIT_CODE

if [ $INSTALLER_EXIT_CODE -ne 0 ]
then
  echo "Installer failed, aborting build"
  exit $INSTALLER_EXIT_CODE
fi

docker-compose up --build installer_npm

docker-compose build php_fpm

docker-compose up --build \
 varnish \
 caddy \
 php_fpm \
 php_fpm_debug \
 sass_dev_builder \
 js_builder \
 redis \
 supervisord \
 --remove-orphans


docker-compose down


