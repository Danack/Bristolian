
cd /var/app/codeview

# echo "Updating/installing npm modules"

# This is done in the installer
# npm update

echo "Running sass builder"

exec npm run sass:build:watch

# tail -f /var/app/README.md
