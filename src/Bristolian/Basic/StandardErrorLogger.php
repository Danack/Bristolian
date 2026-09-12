<?php

namespace Bristolian\Basic;

use function Safe\error_log;

class StandardErrorLogger implements ErrorLogger
{
    /**
     * @codeCoverageIgnore
     */
    public function log(string $string): void
    {
        error_log($string);
    }
}
