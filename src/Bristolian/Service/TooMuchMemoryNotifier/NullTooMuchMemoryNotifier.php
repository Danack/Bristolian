<?php

declare(strict_types = 1);

namespace Bristolian\Service\TooMuchMemoryNotifier;

use Psr\Http\Message\ServerRequestInterface as Request;

class NullTooMuchMemoryNotifier implements TooMuchMemoryNotifier
{
    public function tooMuchMemory(Request $request): void
    {
        // It doesn't do anything?
        // No, it does nothing!
    }
}
