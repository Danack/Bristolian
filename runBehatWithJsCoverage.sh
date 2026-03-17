

set -e
set -x


docker exec -it bristolian-js_builder-1 bash -c "cd /var/app/app && npm run js:build:coverage"

docker exec -it bristolian-php_fpm-1 bash -c "sh runBehat.sh"

docker exec -it bristolian-js_builder-1 bash -c "cd /var/app/app && npm run js:coverage:report";
#
#echo "Now open tmp/behat-js-coverage-report/index.html";
#
## and then open tmp/behat-js-coverage-report/index.html.






