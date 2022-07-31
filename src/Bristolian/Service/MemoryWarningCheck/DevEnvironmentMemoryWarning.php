<?php

declare(strict_types = 1);

namespace Bristolian\Service\MemoryWarningCheck;

use Psr\Http\Message\ServerRequestInterface as Request;

class DevEnvironmentMemoryWarning implements MemoryWarningCheck
{
    public function checkMemoryUsage(Request $request) : int
    {
        $percentMemoryUsed = getPercentMemoryUsed();

        if ($percentMemoryUsed > 50) {
            throw new \Exception("Request is using too much memory.");
        }

        return $percentMemoryUsed;
    }
}
