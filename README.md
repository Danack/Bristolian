# Bristolian

Bristolian.org website



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