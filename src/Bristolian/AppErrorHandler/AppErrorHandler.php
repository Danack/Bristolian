<?php

declare(strict_types = 1);

namespace Bristolian\AppErrorHandler;

/**
 * These are the Slim level error handlers.
 *
 */
interface AppErrorHandler
{
    /**
     * @param mixed $container
     * @return mixed
     */
    public function __invoke($container);
}
