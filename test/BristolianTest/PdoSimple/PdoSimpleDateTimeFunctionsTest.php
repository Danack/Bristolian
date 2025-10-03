<?php

declare(strict_types = 1);

namespace BristolianTest\PdoSimple;

use Bristolian\PdoSimple\PdoSimple;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;

/**
 * @covers \Bristolian\PdoSimple\convertRowToDatetime
 * @covers \Bristolian\PdoSimple\convertRowFromDatetime
 * @group db
 */
class PdoSimpleDateTimeFunctionsTest extends BaseTestCase
{
    use TestPlaceholders;

    public function test_convertRowToDatetime_with_time_columns()
    {
        $row = [
            'id' => 1,
            'test_string' => 'test',
            'created_at' => '2023-01-01 12:00:00',
            'updated_at' => '2023-01-02 13:30:00',
            'start_time' => '2023-01-03 09:15:00',
            'end_time' => '2023-01-03 17:45:00',
        ];

        $result = \Bristolian\PdoSimple\convertRowToDatetime($row);

        $this->assertIsArray($result);
        $this->assertSame(1, $result['id']);
        $this->assertSame('test', $result['test_string']);
        $this->assertInstanceOf(\DateTimeImmutable::class, $result['created_at']);
        $this->assertInstanceOf(\DateTimeImmutable::class, $result['updated_at']);
        $this->assertInstanceOf(\DateTimeImmutable::class, $result['start_time']);
        $this->assertInstanceOf(\DateTimeImmutable::class, $result['end_time']);
        
        $this->assertEquals('2023-01-01 12:00:00', $result['created_at']->format('Y-m-d H:i:s'));
        $this->assertEquals('2023-01-02 13:30:00', $result['updated_at']->format('Y-m-d H:i:s'));
        $this->assertEquals('2023-01-03 09:15:00', $result['start_time']->format('Y-m-d H:i:s'));
        $this->assertEquals('2023-01-03 17:45:00', $result['end_time']->format('Y-m-d H:i:s'));
    }

    public function test_convertRowToDatetime_with_null_values()
    {
        $row = [
            'id' => 1,
            'test_string' => 'test',
            'created_at' => null,
            'updated_at' => '2023-01-02 13:30:00',
            'start_time' => null,
            'end_time' => '2023-01-03 17:45:00',
        ];

        $result = \Bristolian\PdoSimple\convertRowToDatetime($row);

        $this->assertIsArray($result);
        $this->assertSame(1, $result['id']);
        $this->assertSame('test', $result['test_string']);
        $this->assertNull($result['created_at']);
        $this->assertInstanceOf(\DateTimeImmutable::class, $result['updated_at']);
        $this->assertNull($result['start_time']);
        $this->assertInstanceOf(\DateTimeImmutable::class, $result['end_time']);
    }

    public function test_convertRowToDatetime_without_time_columns()
    {
        $row = [
            'id' => 1,
            'test_string' => 'test',
            'some_other_field' => 'value',
        ];

        $result = \Bristolian\PdoSimple\convertRowToDatetime($row);

        $this->assertIsArray($result);
        $this->assertSame(1, $result['id']);
        $this->assertSame('test', $result['test_string']);
        $this->assertSame('value', $result['some_other_field']);
    }

    public function test_convertRowToDatetime_with_invalid_datetime()
    {
        $row = [
            'id' => 1,
            'created_at' => 'invalid datetime',
        ];

        $this->expectException(\Exception::class);
        \Bristolian\PdoSimple\convertRowToDatetime($row);
    }

    public function test_convertRowFromDatetime_with_datetime_objects()
    {
        $now = new \DateTimeImmutable('2023-01-01 12:00:00');
        $row = [
            'id' => 1,
            'test_string' => 'test',
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $result = \Bristolian\PdoSimple\convertRowFromDatetime($row);

        $this->assertIsArray($result);
        $this->assertSame(1, $result['id']);
        $this->assertSame('test', $result['test_string']);
        $this->assertSame('2023-01-01 12:00:00', $result['created_at']);
        $this->assertSame('2023-01-01 12:00:00', $result['updated_at']);
    }

    public function test_convertRowFromDatetime_without_datetime_objects()
    {
        $row = [
            'id' => 1,
            'test_string' => 'test',
            'some_int' => 123,
        ];

        $result = \Bristolian\PdoSimple\convertRowFromDatetime($row);

        $this->assertIsArray($result);
        $this->assertSame(1, $result['id']);
        $this->assertSame('test', $result['test_string']);
        $this->assertSame(123, $result['some_int']);
    }

    public function test_convertRowFromDatetime_with_mixed_types()
    {
        $now = new \DateTimeImmutable('2023-01-01 12:00:00');
        $row = [
            'id' => 1,
            'test_string' => 'test',
            'created_at' => $now,
            'some_int' => 123,
            'some_string' => 'value',
        ];

        $result = \Bristolian\PdoSimple\convertRowFromDatetime($row);

        $this->assertIsArray($result);
        $this->assertSame(1, $result['id']);
        $this->assertSame('test', $result['test_string']);
        $this->assertSame('2023-01-01 12:00:00', $result['created_at']);
        $this->assertSame(123, $result['some_int']);
        $this->assertSame('value', $result['some_string']);
    }

    public function test_datetime_conversion_integration_with_pdo_simple()
    {
        $pdo_simple = $this->injector->make(PdoSimple::class);
        
        // Test inserting with DateTime objects
        $sql = <<< SQL
insert into pdo_simple_test (
    test_string,
    test_int,
    created_at
)
values (
    :test_string,
    :test_int,
    :created_at
)
SQL;

        $test_string = $this->getTestString();
        $now = new \DateTimeImmutable();
        $params = [
            ':test_string' => $test_string,
            ':test_int' => 8888,
            ':created_at' => $now,
        ];

        $insert_id = $pdo_simple->insert($sql, $params);
        $this->assertGreaterThan(0, $insert_id);

        // Test fetching with DateTime conversion
        $fetch_sql = <<< SQL
select 
  id,
  test_string, 
  test_int,
  created_at
from
  pdo_simple_test
where
  id = :id
SQL;

        $result = $pdo_simple->fetchOneAsObjectConstructor(
            $fetch_sql, 
            [':id' => $insert_id], 
            PdoSimpleTestObjectConstructor::class
        );

        $this->assertInstanceOf(PdoSimpleTestObjectConstructor::class, $result);
        $this->assertSame($insert_id, $result->id);
        $this->assertSame($test_string, $result->test_string);
        $this->assertSame(8888, $result->test_int);
        $this->assertInstanceOf(\DateTimeInterface::class, $result->created_at);
        $this->assertEquals($now->format('Y-m-d H:i:s'), $result->created_at->format('Y-m-d H:i:s'));
    }
}
