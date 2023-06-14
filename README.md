# Bristolian

Bristolian.org website


## Where important bits of code live

Javascript source code - /app/public/tsx
Javascript tests - /app/public/tsx
CSS/Sass source - /app/public/scss
Compiled CSS - /app/public/css
Compiled JS - /app/public/js
PHP source code - /src
PHP tests - /test


## Bashing into a box:

```
docker exec -it bristolian-js_builder-1 bash

npm run test
```



or to run PHP Unit tests:

```
docker exec -it bristolian-php_fpm_debug-1 bash


sh runUnitTests.sh
```

Or
```
sh runUnitTests.sh --group wip
```

## Docker sometimes needs cleaning

```
docker update --restart=no $(docker ps -a -q)

docker rm $(docker ps -a -q)
docker rmi $(docker images -q)
docker network rm $(docker network ls -q)

docker update --restart=no my-container
```


## Access MySql

mysql -uroot -pPrJaGpnNSzSLW8p8 -h127.0.0.1

## Resetting the database

Just bring your docker boxes down, delete the directory 

## Various links

https://www.lipsum.com/
https://jestjs.io/
