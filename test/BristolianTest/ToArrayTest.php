<?php

namespace BristolianTest;

/**
 * @coversNothing
 *
 *
 */

use BristolianTest\TestFixtures\ToArrayClass;
use BristolianTest\TestFixtures\NestedToArrayClass;
use BristolianTest\TestFixtures\ToArrayClassWithDatetime;

/**
 * @coversNothing
 */
class ToArrayTest extends BaseTestCase
{
    /**
     * @group wip
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
     * @throws \Bristolian\BristolianException
     */
    public function test_works_with_datetime()
    {
        $string_value = "John";
        $date_value = new \DateTimeImmutable("2010-01-28T15:00:00+02:00");

        $object = new ToArrayClassWithDatetime($string_value, $date_value);

        $result = $object->toArray();

        $expected = [
            "foo" => "John",
            "dateTime" => "2010-01-28T15:00:00.000000+02:00"
        ];

        $this->assertSame($expected, $result);
    }
}
