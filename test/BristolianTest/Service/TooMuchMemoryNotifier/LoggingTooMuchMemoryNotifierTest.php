<?php

declare(strict_types=1);

namespace BristolianTest\Service\TooMuchMemoryNotifier;

use Bristolian\Service\CliOutput\CapturingCliOutput;
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
     * @covers \Bristolian\Service\TooMuchMemoryNotifier\LoggingTooMuchMemoryNotifier::__construct
     * @covers \Bristolian\Service\TooMuchMemoryNotifier\LoggingTooMuchMemoryNotifier::tooMuchMemory
     */
    public function test_tooMuchMemory_logs_request_path(): void
    {
        $cliOutput = new CapturingCliOutput();
        $notifier = new LoggingTooMuchMemoryNotifier($cliOutput);
        $request = new ServerRequest(
            [],
            [],
            new Uri('https://example.com/some/path'),
            'GET'
        );
        $notifier->tooMuchMemory($request);

        $errorLines = $cliOutput->getCapturedErrorLines();
        $this->assertCount(1, $errorLines);
        $this->assertStringContainsString('Request is using too much memory', $errorLines[0]);
        $this->assertStringContainsString('/some/path', $errorLines[0]);
    }
}
