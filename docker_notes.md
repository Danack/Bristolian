# Docker notes


## Cleaning everything


Very, very occasionally, something may go wrong with the docker boxes, or you might want to reclaim some of the hard disk space used by docker container images. The following commands should be run to destroy all boxes.

```
docker rm -f $(docker ps -a -q)
docker rmi -f $(docker images -q)
docker network rm $(docker network ls -q)
```

You can view details, including names, of the running docker containers with:
```
docker ps
```

You can bash into a running box with:

```
docker exec -it example_web_admin_1 bash
```

docker-compose exec php_fpm bash buildSassToCss.sh
docker-compose exec php_fpm bash tool/js_compress.sh


## Box automatically starting

Apparently docker sometimes gets confused and thinks it should automatically run all the containers that have ever been run on the computer. Which causes some issues.

```
docker update --restart=no $(docker ps -a -q)
```

That command will tell docker to stop automatically starting all the containers that are running.