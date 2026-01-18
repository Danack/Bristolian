<?php

namespace BristolianTest\CliController;

use BristolianTest\BaseTestCase;
use Bristolian\CliController\Debug;

/**
 * @coversNothing
 * @group wip
 */
class DebugTest extends BaseTestCase
{
    public function test_send_message_to_room()
    {
        $this->markTestSkipped('Test not implemented yet');

        $this->injector->defineParam('message', "Woot this is a test message");
        $this->injector->execute([Debug::class, 'send_message_to_room']);
    }
}
