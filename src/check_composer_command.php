<?php

declare(strict_types = 1);

// Used in containers/installer/entrypoint.sh to figure out what type of
// composer update/install to run. Could probably be changed to bash if
// anyone cares to do so.

// @codeCoverageIgnoreEnd
// This code is not unit tested.

$env = getenv();

if (array_key_exists('ENV_DESCRIPTION', $env) === false) {
    echo "env description not set.\n";
    exit(-1);
}

$env_description = $env['ENV_DESCRIPTION'];
$env_parts = explode(",", $env_description);

if (in_array('local', $env_parts, true) === true) {
    echo "update";
    exit(0);
}

echo "prod";
exit(0);

// @codeCoverageIgnoreEnd
