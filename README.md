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


## Various links

https://www.lipsum.com/
https://jestjs.io/
