<?php

declare(strict_types = 1);

namespace Bristolian\Service\MemoryWarningCheck;

use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @codeCoverageIgnore
 */
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
        [$percentMemoryUsed, $memoryLimitValue] = getPercentMemoryUsed();

        if ($percentMemoryUsed > 50) {
            // @codeCoverageIgnoreStart
            $this->tooMuchMemoryNotifier->tooMuchMemory($request);
            // @codeCoverageIgnoreEnd
        }

        return $percentMemoryUsed;
    }
}
