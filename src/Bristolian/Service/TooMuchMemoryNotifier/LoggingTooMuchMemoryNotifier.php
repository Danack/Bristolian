<?php

namespace Bristolian\Service\TooMuchMemoryNotifier;

use Bristolian\Service\CliOutput\CliOutput;
use Psr\Http\Message\ServerRequestInterface as Request;

class LoggingTooMuchMemoryNotifier implements TooMuchMemoryNotifier
{
    public function __construct(private CliOutput $cliOutput)
    {
    }

    public function tooMuchMemory(Request $request): void
    {
        $message = sprintf(
            "Request is using too much memory. Path was [%s]",
            $request->getUri()->getPath()
        );

        $this->cliOutput->writeError($message);
    }
}
