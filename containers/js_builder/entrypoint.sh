
cd /var/app/app

echo "Updating/installing npm modules"


npm update

# CMD sh /var/app/containers/js_build/entrypoint.sh

npm run js:build:dev:watch
# tail -f /var/app/README.md
