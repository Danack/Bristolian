<?php

declare(strict_types=1);

namespace BristolianTest\Service\TooMuchMemoryNotifier;

use Bristolian\Service\CliOutput\CapturingCliOutput;
use Bristolian\Service\TooMuchMemoryNotifier\LoggingTooMuchMemoryNotifier;
use BristolianTest\BaseTestCase;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\TooMuchMemoryNotifier\LoggingTooMuchMemoryNotifier::class, '__construct')]
#[CoversMethod(\Bristolian\Service\TooMuchMemoryNotifier\LoggingTooMuchMemoryNotifier::class, 'tooMuchMemory')]

class LoggingTooMuchMemoryNotifierTest extends BaseTestCase
{
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
