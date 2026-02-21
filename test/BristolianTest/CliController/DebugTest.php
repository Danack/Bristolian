<?php

namespace BristolianTest\CliController;

use BristolianTest\BaseTestCase;
use Bristolian\CliController\Debug;

/**
 * @coversNothing
 */
class DebugTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\CliController\Debug::hello
     */
    public function test_hello_outputs_hello(): void
    {
        $debug = $this->injector->make(Debug::class);
        ob_start();
        $debug->hello();
        $output = ob_get_clean();
        $this->assertSame('Hello.', trim($output));
    }

    /**
     * @covers \Bristolian\CliController\Debug::stack_trace
     * @covers \Bristolian\CliController\fn_level_1
     * @covers \Bristolian\CliController\fn_level_2
     * @covers \Bristolian\CliController\fn_level_3
     */
    public function test_stack_trace_throws_from_inner_function(): void
    {
        $debug = $this->injector->make(Debug::class);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This is on line');
        $debug->stack_trace();
    }

    public function test_send_message_to_room(): void
    {
        $this->markTestSkipped('Test not implemented yet');

        $this->injector->defineParam('message', "Woot this is a test message");
        $this->injector->execute([Debug::class, 'send_message_to_room']);
    }
}
