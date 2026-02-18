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



if [ "$LOCAL" = "$REMOTE" ]; then
    echo "Up-to-date at ${timestamp}"
elif [ "$LOCAL" = "$BASE" ]; then
    echo "Need to pull at ${timestamp}"

    git pull
    if [ $? -ne 0 ]; then
        {
            echo "git pull failed at $(date)"
            echo "Retrying git pull..."
            git pull 2>&1
        } > /var/home/Bristolian/Bristolian/data/git_pull_error.log

      echo "Aborting deploy."
    fi

    # chown -R deployer:deployer *
    sh runProd.sh
    MESSAGE=$(git show -s --format=%s $REMOTE)
    echo "Should have deployed ${REMOTE} ${MESSAGE} at $(date)"
    rm /var/home/Bristolian/Bristolian/data/git_pull_error.log 2>/dev/null
elif [ "$REMOTE" = "$BASE" ]; then
    echo "Need to push server changes."

    {
        echo "Project files have been edited on the server at $(date)"
        echo "Cannot deploy"
        git pull 2>&1
    } > /var/home/Bristolian/Bristolian/data/git_pull_error.log


else
    echo "Diverged"
    {
        echo "Project files have diverged on the server at $(date)"
        echo "Cannot deploy"
        git pull 2>&1
    } > /var/home/Bristolian/Bristolian/data/git_pull_error.log
fi