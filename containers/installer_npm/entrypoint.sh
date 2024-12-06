
cd /var/app/app


if test -f "./delete_this_to_update_npm_modules.txt"; then
    echo "delete_this_to_update_npm_modules.txt already exists, skipping."
    exit 0
fi

touch delete_this_to_update_npm_modules.txt

FILE=$(basename "$0")

echo "This file is checked to exist by the script ${FILE} by the 'installer_npm' container. If it exists, then 'npm update' is not run." > delete_this_to_update_npm_modules.txt
echo "" >> delete_this_to_update_npm_modules.txt
echo "Deleting it will cause 'npm update' the next time the 'installer_npm' container is run." >> delete_this_to_update_npm_modules.txt

echo "Updating/installing npm modules"

npm update

echo "npm modules should be updated"
