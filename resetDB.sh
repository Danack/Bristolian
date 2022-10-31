#!/usr/bin/env bash

php vendor/bin/phinx migrate -e internal -t 0
php vendor/bin/phinx migrate -e internal
php cli.php seed:initial
