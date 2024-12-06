<?php

namespace Bristolian\Basic;

class StandardErrorLogger implements ErrorLogger
{
    /**
     * @codeCoverageIgnore
     */
    public function log(string $string): void
    {
        \error_log($string);
    }
}
