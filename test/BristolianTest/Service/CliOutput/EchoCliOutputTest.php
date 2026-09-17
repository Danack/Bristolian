<?php

declare(strict_types=1);

namespace BristolianTest\Service\CliOutput;

use Bristolian\Service\CliOutput\EchoCliOutput;
use BristolianTest\BaseTestCase;
use function Safe\ob_get_clean;
use function Safe\ob_start;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\CliOutput\EchoCliOutput::class, 'write')]
#[CoversMethod(\Bristolian\Service\CliOutput\EchoCliOutput::class, 'writeError')]

class EchoCliOutputTest extends BaseTestCase
{
    public function test_write_echoes_message(): void
    {
        $output = new EchoCliOutput();

        ob_start();
        $output->write('hello');
        $out = ob_get_clean();

        $this->assertSame('hello', $out);
    }

    public function test_write_echoes_multiple_calls_without_separator(): void
    {
        $output = new EchoCliOutput();

        ob_start();
        $output->write('one');
        $output->write('two');
        $out = ob_get_clean();

        $this->assertSame('onetwo', $out);
    }

//    /**
//     */
//    public function test_writeError_invokes_error_log_without_throwing(): void
//    {
////        $old = ini_get('error_log');
////        ini_set('error_log', '/dev/null');
////
////        try {
////            // run code that calls error_log()
////        } finally {
////            ini_set('error_log', $old);
////        }
////
//
//        $output = new EchoCliOutput();
//        $output->writeError('test error message');
//        $this->addToAssertionCount(1);
//    }
}
