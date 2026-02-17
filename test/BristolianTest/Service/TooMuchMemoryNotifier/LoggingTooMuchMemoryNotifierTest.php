<?php

declare(strict_types=1);

namespace BristolianTest\Service\TooMuchMemoryNotifier;

use Bristolian\Service\TooMuchMemoryNotifier\LoggingTooMuchMemoryNotifier;
use BristolianTest\BaseTestCase;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Uri;

/**
 * @coversNothing
 */
class LoggingTooMuchMemoryNotifierTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\TooMuchMemoryNotifier\LoggingTooMuchMemoryNotifier::tooMuchMemory
     */
    public function test_tooMuchMemory_logs_request_path(): void
    {
        $notifier = new LoggingTooMuchMemoryNotifier();
        $request = new ServerRequest(
            [],
            [],
            new Uri('https://example.com/some/path'),
            'GET'
        );
        $notifier->tooMuchMemory($request);
        $this->addToAssertionCount(1);
    }
}
