#!/bin/sh

# Add this to cron
#
#
# */2 * * * * /usr/bin/flock -w 0 /var/home/Bristolian/Bristolian/cron.lock /bin/sh /var/home/Bristolian/Bristolian/update.sh >> /var/log/deployer/bristolian.log 2>&1


cd /var/home/Bristolian/Bristolian

git fetch

UPSTREAM=${1:-'@{u}'}
LOCAL=$(git rev-parse @)
REMOTE=$(git rev-parse "$UPSTREAM")
BASE=$(git merge-base @ "$UPSTREAM")

if [ $LOCAL = $REMOTE ]; then
    echo "Up-to-date"
elif [ $LOCAL = $BASE ]; then
    echo "Need to pull"
    git pull
    chown -R deployer:deployer *
    sh runProd.sh
elif [ $REMOTE = $BASE ]; then
    echo "Need to push"
else
    echo "Diverged"
fi