<?php

declare(strict_types=1);

namespace BristolianTest\Service\CliOutput;

use Bristolian\Service\CliOutput\EchoCliOutput;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class EchoCliOutputTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\CliOutput\EchoCliOutput::write
     */
    public function test_write_echoes_message(): void
    {
        $output = new EchoCliOutput();

        ob_start();
        $output->write('hello');
        $out = ob_get_clean();

        $this->assertSame('hello', $out);
    }

    /**
     * @covers \Bristolian\Service\CliOutput\EchoCliOutput::write
     */
    public function test_write_echoes_multiple_calls_without_separator(): void
    {
        $output = new EchoCliOutput();

        ob_start();
        $output->write('one');
        $output->write('two');
        $out = ob_get_clean();

        $this->assertSame('onetwo', $out);
    }
}
