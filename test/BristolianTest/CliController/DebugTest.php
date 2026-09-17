<?php

namespace BristolianTest\CliController;

use BristolianTest\BaseTestCase;
use Bristolian\CliController\Debug;
use function Safe\ob_get_clean;
use function Safe\ob_start;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\CliController\Debug::class, 'hello')]
#[CoversMethod(\Bristolian\CliController\Debug::class, 'stack_trace')]
#[CoversFunction('Bristolian\CliController\fn_level_1')]
#[CoversFunction('Bristolian\CliController\fn_level_2')]
#[CoversFunction('Bristolian\CliController\fn_level_3')]

class DebugTest extends BaseTestCase
{
    public function test_hello_outputs_hello(): void
    {
        $debug = $this->injector->make(Debug::class);
        ob_start();
        $debug->hello();
        $output = ob_get_clean();
        $this->assertSame('Hello.', trim($output));
    }

    public function test_stack_trace_throws_from_inner_function(): void
    {
        $debug = $this->injector->make(Debug::class);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This is on line');
        $debug->stack_trace();
    }
}
