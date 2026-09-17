#!/usr/bin/env bash

set -e
set -x

sh runPhpStan.sh
sh runUnitTests.sh

# php test/check_site.php
