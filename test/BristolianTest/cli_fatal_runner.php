<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

// Register shutdown so when script halts after E_USER_ERROR, our handler runs
// and error_get_last() will then contain the error.
\Bristolian\CLIFunction::setupErrorHandlers();
trigger_error('fatal test message', E_USER_ERROR);
