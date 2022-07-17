#!/bin/sh -l

set -e
# set -x
# pwd
# ls -l

echo '---Installing dependencies---'
php ./composer.phar install

echo '---Checking SCSS colors'
php ./test/checkScssColors.php

echo '---Running unit tests---'
bash runTests.sh