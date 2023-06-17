
cd /var/app/app


if test -f "./delete_this_to_update_npm_modules.txt"; then
    echo "delete_this_to_update_npm_modules.txt already exists, skipping."
    exit 0
fi

touch delete_this_to_update_npm_modules.txt

echo "Updating/installing npm modules"

npm update

echo "npm modules should be updated"
