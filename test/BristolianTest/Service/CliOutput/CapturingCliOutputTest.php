<?php

declare(strict_types=1);

namespace BristolianTest\Service\CliOutput;

use Bristolian\Service\CliOutput\CapturingCliOutput;
use Bristolian\Service\CliOutput\CliExitRequestedException;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class CapturingCliOutputTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\CliOutput\CapturingCliOutput::write
     * @covers \Bristolian\Service\CliOutput\CapturingCliOutput::getCapturedLines
     * @covers \Bristolian\Service\CliOutput\CapturingCliOutput::getCapturedOutput
     */
    public function test_write_captures_messages(): void
    {
        $output = new CapturingCliOutput();
        $output->write("line one");
        $output->write("line two");

        $this->assertSame(['line one', 'line two'], $output->getCapturedLines());
        $this->assertSame('line oneline two', $output->getCapturedOutput());
    }

    /**
     * @covers \Bristolian\Service\CliOutput\CapturingCliOutput::exit
     */
    public function test_exit_throws_with_code(): void
    {
        $output = new CapturingCliOutput();

        $this->expectException(CliExitRequestedException::class);
        $this->expectExceptionMessage('CLI exit requested');
        $output->exit(42);
    }

    /**
     * @covers \Bristolian\Service\CliOutput\CliExitRequestedException::getExitCode
     */
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
