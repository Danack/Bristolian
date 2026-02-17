<?php

declare(strict_types=1);

namespace BristolianTest\Service\TooMuchMemoryNotifier;

use Bristolian\Service\TooMuchMemoryNotifier\NullTooMuchMemoryNotifier;
use BristolianTest\BaseTestCase;
use Laminas\Diactoros\ServerRequest;

/**
 * @coversNothing
 */
class NullTooMuchMemoryNotifierTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\TooMuchMemoryNotifier\NullTooMuchMemoryNotifier::tooMuchMemory
     */
    public function test_tooMuchMemory_does_nothing(): void
    {
        $notifier = new NullTooMuchMemoryNotifier();
        $request = new ServerRequest();
        $notifier->tooMuchMemory($request);
        $this->addToAssertionCount(1);
    }
}
