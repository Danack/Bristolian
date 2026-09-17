<?php

declare(strict_types=1);

namespace BristolianTest\Service\CliOutput;

use Bristolian\Service\CliOutput\CapturingCliOutput;
use Bristolian\Service\CliOutput\CliExitRequestedException;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\CliOutput\CapturingCliOutput::class, 'exit')]
#[CoversMethod(\Bristolian\Service\CliOutput\CapturingCliOutput::class, 'getCapturedErrorLines')]
#[CoversMethod(\Bristolian\Service\CliOutput\CapturingCliOutput::class, 'getCapturedLines')]
#[CoversMethod(\Bristolian\Service\CliOutput\CapturingCliOutput::class, 'getCapturedOutput')]
#[CoversMethod(\Bristolian\Service\CliOutput\CapturingCliOutput::class, 'write')]
#[CoversMethod(\Bristolian\Service\CliOutput\CapturingCliOutput::class, 'writeError')]
#[CoversMethod(\Bristolian\Service\CliOutput\CliExitRequestedException::class, 'getExitCode')]

class CapturingCliOutputTest extends BaseTestCase
{
    public function test_write_captures_messages(): void
    {
        $output = new CapturingCliOutput();
        $output->write("line one");
        $output->write("line two");

        $this->assertSame(['line one', 'line two'], $output->getCapturedLines());
        $this->assertSame('line oneline two', $output->getCapturedOutput());
    }

    public function test_writeError_captures_error_messages(): void
    {
        $output = new CapturingCliOutput();
        $output->writeError('error one');
        $output->writeError('error two');

        $this->assertSame(['error one', 'error two'], $output->getCapturedErrorLines());
    }

    public function test_exit_throws_with_code(): void
    {
        $output = new CapturingCliOutput();

        $this->expectException(CliExitRequestedException::class);
        $this->expectExceptionMessage('CLI exit requested');
        $output->exit(42);
    }

    public function test_exit_exception_has_exit_code(): void
    {
        $output = new CapturingCliOutput();

        try {
            $output->exit(7);
            $this->fail('Expected CliExitRequestedException');
        } catch (CliExitRequestedException $e) {
            $this->assertSame(7, $e->getExitCode());
        }
    }
}
