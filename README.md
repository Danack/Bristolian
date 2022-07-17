# Bristolian
Bristolian.org website





docker update --restart=no $(docker ps -a -q)

docker rm $(docker ps -a -q)
docker rmi $(docker images -q)
docker network rm $(docker network ls -q)

docker update --restart=no my-container