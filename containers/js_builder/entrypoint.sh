
cd /var/app/app

echo "Updating/installing npm modules"

echo "lets not update for a while"
# npm update

# CMD sh /var/app/containers/js_build/entrypoint.sh

exec npm run js:build:dev:watch
# tail -f /var/app/README.md
