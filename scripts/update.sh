#!/bin/sh

# Add this to cron
#
#
# */2 * * * * /usr/bin/flock -w 0 /var/home/Bristolian/Bristolian/cron.lock /bin/sh /var/home/Bristolian/Bristolian/scripts/update.sh >> /var/log/deployer/bristolian.log 2>&1
#
# If someone does a manual pull, use this to restore file permissions
# chown deployer -R .
#
# ln -s /var/log/deployer/bristolian.log /var/home/Bristolian/Bristolian/deployer.log
#
# su - deployer -c "ln /var/log/deployer/bristolian.log /var/home/Bristolian/Bristolian/deployer.log"


cd /var/home/Bristolian/Bristolian

git fetch

UPSTREAM=${1:-'@{u}'}
LOCAL=$(git rev-parse @)
REMOTE=$(git rev-parse "$UPSTREAM")
BASE=$(git merge-base @ "$UPSTREAM")

timestamp=$(date +"%Y-%m-%d_%H-%M-%S")

# echo "LOCAL = ${LOCAL}";
# echo "REMOTE = ${REMOTE}";
# echo "BASE = ${BASE}";

if [ $LOCAL = $REMOTE ]; then
    echo "Up-to-date at ${timestamp}"
elif [ $LOCAL = $BASE ]; then
    echo "Need to pull at ${timestamp}"
    git pull
    chown -R deployer:deployer *
    sh runProd.sh
elif [ $REMOTE = $BASE ]; then
    echo "Need to push."
else
    echo "Diverged"
fi