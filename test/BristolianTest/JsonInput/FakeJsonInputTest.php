<?php

declare(strict_types = 1);

namespace BristolianTest\JsonInput;

use BristolianTest\BaseTestCase;
use Bristolian\JsonInput\FakeJsonInput;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\JsonInput\FakeJsonInput::class)]

class FakeJsonInputTest extends BaseTestCase
{
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
