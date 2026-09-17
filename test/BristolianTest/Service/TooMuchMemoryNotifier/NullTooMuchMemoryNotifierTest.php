<?php

declare(strict_types=1);

namespace BristolianTest\Service\TooMuchMemoryNotifier;

use Bristolian\Service\TooMuchMemoryNotifier\NullTooMuchMemoryNotifier;
use BristolianTest\BaseTestCase;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\TooMuchMemoryNotifier\NullTooMuchMemoryNotifier::class, 'tooMuchMemory')]

class NullTooMuchMemoryNotifierTest extends BaseTestCase
{
    public function test_tooMuchMemory_does_nothing(): void
    {
        $notifier = new NullTooMuchMemoryNotifier();
        $request = new ServerRequest();
        $notifier->tooMuchMemory($request);
        $this->addToAssertionCount(1);
    }
}
