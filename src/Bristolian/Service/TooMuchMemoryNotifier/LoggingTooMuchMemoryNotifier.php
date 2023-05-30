<?php

namespace Bristolian\Service\TooMuchMemoryNotifier;

use Psr\Http\Message\ServerRequestInterface as Request;

class LoggingTooMuchMemoryNotifier implements TooMuchMemoryNotifier
{
    public function tooMuchMemory(Request $request): void
    {
        // It doesn't do anything?
        // No, it does nothing!

        $message = sprintf(
            "Request is using too much memory. Path was [%s]",
            $request->getUri()->getPath()
        );

        \error_log($message);
    }
}
