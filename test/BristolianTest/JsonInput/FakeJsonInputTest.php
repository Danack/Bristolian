<?php

declare(strict_types = 1);

namespace BristolianTest\JsonInput;

use BristolianTest\BaseTestCase;
use Bristolian\JsonInput\FakeJsonInput;

/**
 * @coversNothing
 */
class FakeJsonInputTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\JsonInput\FakeJsonInput
     */
    public function testBasic(): void
    {
        $data = ['foo' => 'bar'];
        $jsonInput = new FakeJsonInput($data);

        $this->assertSame(
            $data,
            $jsonInput->getData()
        );
    }
}
