<?php

declare(strict_types = 1);

namespace Bristolian\Service\MemoryWarningCheck;

use Psr\Http\Message\ServerRequestInterface as Request;

class DevEnvironmentMemoryWarning implements MemoryWarningCheck
{
    public function checkMemoryUsage(Request $request) : int
    {
        [$percentMemoryUsed, $memoryLimitValue] = getPercentMemoryUsed();

        if ($percentMemoryUsed > 75) {
            $message = sprintf(
                "Request is using too much memory: %d of memory limit %s.",
                $percentMemoryUsed,
                human_readable_value($memoryLimitValue)
            );
            throw new MemoryUseException();
        }

        return $percentMemoryUsed;
    }
}
