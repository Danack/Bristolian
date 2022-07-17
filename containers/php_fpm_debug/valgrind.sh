#!/bin/bash

# This file adds the option to suppress the errors listed in the
# valgrind.supp file by default to any usage of valgrind.
/usr/bin/valgrind --suppressions=/var/app/containers/php_fpm_debug/valgrind.supp $@


