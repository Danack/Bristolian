
cd /var/app/app

echo "Updating/installing npm modules"

npm update

echo "Updated. Running sass builder"

npm run sass:build:watch

# tail -f /var/app/README.md
