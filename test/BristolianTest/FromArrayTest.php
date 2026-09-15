<?php

namespace BristolianTest;

use PHPUnit\Framework\Attributes\CoversNothing;
use Bristolian\FromArray;
use BristolianTest\BaseTestCase;

/**
 * Test class that uses the FromArray trait
 *
 * @coversNothing
 */
class TestFromArrayClass
{
    use FromArray;

    /** @var string */
    public $name;
    /** @var int */
    public $age;
    /** @var bool */
    public $active;
}

#[CoversNothing]
class FromArrayTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\FromArray
     */
    public function testWorks()
    {
        $data = [
            'name' => 'John Doe',
            'age' => 30,
            'active' => true,
        ];

        $instance = TestFromArrayClass::fromArray($data);

        $this->assertInstanceOf(TestFromArrayClass::class, $instance);
        $this->assertSame('John Doe', $instance->name);
        $this->assertSame(30, $instance->age);
        $this->assertTrue($instance->active);
    }
}
