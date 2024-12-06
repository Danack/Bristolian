<?php

namespace Bristolian\Basic;

/**
 * A very simple error logger to avoid directly calling
 * "\error_log, so that code can be tested.
 */
interface ErrorLogger
{
    public function log(string $string): void;
}
