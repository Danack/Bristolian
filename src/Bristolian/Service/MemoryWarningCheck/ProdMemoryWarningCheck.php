<?php

declare(strict_types = 1);

namespace Bristolian\Service\MemoryWarningCheck;

use Psr\Http\Message\ServerRequestInterface as Request;

class ProdMemoryWarningCheck implements MemoryWarningCheck
{
    /** @var \Bristolian\Service\TooMuchMemoryNotifier\TooMuchMemoryNotifier */
    private $tooMuchMemoryNotifier;

    /**
     *
     * @param \Bristolian\Service\TooMuchMemoryNotifier\TooMuchMemoryNotifier $tooMuchMemoryNotifier
     */
    public function __construct(\Bristolian\Service\TooMuchMemoryNotifier\TooMuchMemoryNotifier $tooMuchMemoryNotifier)
    {
        $this->tooMuchMemoryNotifier = $tooMuchMemoryNotifier;
    }

    public function checkMemoryUsage(Request $request) : int
    {
        $percentMemoryUsed = getPercentMemoryUsed();

        if ($percentMemoryUsed > 50) {
            $message = sprintf(
                "Request is using too much memory. Path was [%s]",
                $request->getUri()->getPath()
            );

            \error_log($message);
        }

        return $percentMemoryUsed;
    }
}
