<?php

declare(strict_types = 1);

namespace BristolianChatTest;


use BristolianTest\BaseTestCase;


/**
 * @coversNothing
 */
class FunctionsChatTest extends BaseTestCase
{

    /**
     * @covers ::generateFakeChatMessage
     */
    public function test_generateFakeChatMessage(): void
    {
        $message = generateFakeChatMessage();
    }


}
