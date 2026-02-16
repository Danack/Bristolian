<?php

namespace BristolianTest;

/**
 * @coversNothing
 *
 *
 */

use BristolianTest\TestFixtures\NestedToArrayClass;
use BristolianTest\TestFixtures\ToArrayClass;
use BristolianTest\TestFixtures\ToArrayClassWithDatetime;
use BristolianTest\TestFixtures\ToArrayClassWithSkippedProperty;
use BristolianTest\TestFixtures\ToArrayClassWithUnsupportedProperty;
use Bristolian\Exception\BristolianException;

/**
 * @coversNothing
 * @group wip
 */
class ToArrayTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\ToArray
     */
    public function test_works()
    {
        $string_value = "John";
        $int_value = 12345;

        $object = new ToArrayClass($string_value, $int_value);
        $result = $object->toArray();

        $expected = [
            'foo' => "John",
            'bar' => 12345
        ];

        $this->assertSame($expected, $result);

        $nested_object = new NestedToArrayClass($string_value, $int_value, $object);

        $expected_nested = [
            'foo' => "John",
            'bar' => 12345,
            'instance' => $expected
        ];

        $this->assertSame($expected_nested, $nested_object->toArray());
    }


    /**
     * @covers \Bristolian\ToArray
     * @return void
     * @throws \Bristolian\Exception\BristolianException
     */
    public function test_works_with_datetime()
    {
        $string_value = "John";
        $date_value = new \DateTimeImmutable("2010-01-28T15:00:00+02:00");

        $object = new ToArrayClassWithDatetime($string_value, $date_value);

        $result = $object->toArray();

        $expected = [
            "foo" => "John",
            "dateTime" => '2010-01-28T15:00:00+02:00'
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * @covers \Bristolian\ToArray
     */
    public function test_properties_starting_with_double_underscore_are_skipped(): void
    {
        $object = new ToArrayClassWithSkippedProperty('visible', 'ignored');
        $result = $object->toArray();

        $this->assertSame(['foo' => 'visible'], $result);
        $this->assertArrayNotHasKey('__internal', $result);
    }

    /**
     * @covers \Bristolian\ToArray
     */
    public function test_throws_BristolianException_when_property_cannot_be_converted(): void
    {
        $object = new ToArrayClassWithUnsupportedProperty('foo', new \stdClass());

        $this->expectException(BristolianException::class);
        $this->expectExceptionMessage('Failed to convert object to array on item [unsupported]');

        $object->toArray();
    }
}
