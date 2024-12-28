<?php

namespace Bristolian\Service\MemoryWarningCheck;

use Psr\Http\Message\ServerRequestInterface as Request;

class FakeMemoryWarningCheck implements MemoryWarningCheck
{
    public function __construct(private int $percentage_used)
    {
    }

    public function checkMemoryUsage(Request $request): int
    {
        return $this->percentage_used;
    }
}
