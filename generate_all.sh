#!/usr/bin/env bash

set -e

php cli.php generate:javascript_constants
php cli.php generate:php_response_types
php cli.php generate:php_table_helper_classes
php cli.php generate:typescript_api_routes