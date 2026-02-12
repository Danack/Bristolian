<?php

namespace BristolianTest;

use Bristolian\FromString;
use BristolianTest\BaseTestCase;

/**
 * Test class that uses the FromString trait with constructor
 *
 * @coversNothing
 */
class TestFromStringClass
{
    use FromString;

    public function __construct(
        public readonly string $name,
        public readonly int $age,
        public readonly bool $active = false,
        public readonly ?\DateTimeInterface $created_at = null,
    ) {
    }
}

/**
 * Test class that uses the FromString trait without constructor
 *
 * @coversNothing
 */
class TestFromStringNoConstructorClass
{
    use FromString;
}

/**
 * @coversNothing
 */
class FromStringTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\FromString
     */
    public function testWorks_fromArray_with_all_parameters()
    {
        $data = [
            'name' => 'Jane Doe',
            'age' => 25,
            'active' => true,
        ];

        $instance = TestFromStringClass::fromArray($data);

        $this->assertInstanceOf(TestFromStringClass::class, $instance);
        $this->assertSame('Jane Doe', $instance->name);
        $this->assertSame(25, $instance->age);
        $this->assertTrue($instance->active);
    }

    /**
     * @covers \Bristolian\FromString
     */
    public function testWorks_fromArray_with_default_values()
    {
        $data = [
            'name' => 'Bob Smith',
            'age' => 40,
        ];

        $instance = TestFromStringClass::fromArray($data);

        $this->assertInstanceOf(TestFromStringClass::class, $instance);
        $this->assertSame('Bob Smith', $instance->name);
        $this->assertSame(40, $instance->age);
        $this->assertFalse($instance->active); // default value
    }

    /**
     * @covers \Bristolian\FromString
     */
    public function testWorks_fromArray_with_datetime_conversion()
    {
        $data = [
            'name' => 'Alice',
            'age' => 30,
            'created_at' => '2024-01-15 12:00:00',
        ];

        $instance = TestFromStringClass::fromArray($data);

        $this->assertInstanceOf(TestFromStringClass::class, $instance);
        $this->assertInstanceOf(\DateTimeInterface::class, $instance->created_at);
        $this->assertSame('2024-01-15 12:00:00', $instance->created_at->format('Y-m-d H:i:s'));
    }

    /**
     * @covers \Bristolian\FromString
     */
    public function testWorks_fromArray_without_constructor()
    {
        $data = [];

        $instance = TestFromStringNoConstructorClass::fromArray($data);

        $this->assertInstanceOf(TestFromStringNoConstructorClass::class, $instance);
    }

    /**
     * @covers \Bristolian\FromString
     */
    public function testWorks_fromArray_fails_with_missing_required_parameter()
    {
        $data = [
            'name' => 'Test',
            // missing 'age'
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Missing required key 'age'");

        TestFromStringClass::fromArray($data);
    }

    /**
     * @covers \Bristolian\FromString
     */
    public function testWorks_fromArray_fails_with_invalid_datetime()
    {
        $data = [
            'name' => 'Test',
            'age' => 30,
            'created_at' => 'invalid datetime',
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid datetime format for 'created_at'");

        TestFromStringClass::fromArray($data);
    }

    /**
     * @covers \Bristolian\FromString
     */
    public function testWorks_fromString()
    {
        $json = json_encode([
            'name' => 'Charlie',
            'age' => 35,
            'active' => true,
        ]);

        $instance = TestFromStringClass::fromString($json);

        $this->assertInstanceOf(TestFromStringClass::class, $instance);
        $this->assertSame('Charlie', $instance->name);
        $this->assertSame(35, $instance->age);
        $this->assertTrue($instance->active);
    }
}
