<?php

declare(strict_types = 1);

namespace BristolianTest\PdoSimple;

use Bristolian\PdoSimple\PdoSimple;
use Bristolian\PdoSimple\PdoSimpleException;
use Bristolian\PdoSimple\PdoSimpleWithPreviousException;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;
use Bristolian\Database\pdo_simple_test;
use Bristolian\PdoSimple\RowNotFoundException;
use BristolianTest\PdoSimple\PdoSimpleTestObjectConstructor;
use Ramsey\Uuid\Uuid;

/**
 * @covers \Bristolian\PdoSimple\PdoSimple
 * @group db
 */
class PdoSimpleTest extends BaseTestCase
{
    use TestPlaceholders;

    public function testWorks_fetchAllAsObject()
    {
        $pdo_simple = $this->injector->make(PdoSimple::class);

        $sql = <<< SQL
select 
  id,
  test_string, 
  test_int,
  created_at
from
  pdo_simple_test
order by id ASC
limit 2

SQL;

        $test_objects = $pdo_simple->fetchAllAsObject(
            $sql,
            [],
            PdoSimpleTestObject::class
        );

        $this->assertCount(2, $test_objects);
        foreach ($test_objects as $test_object) {
            $this->assertInstanceOf(PdoSimpleTestObject::class, $test_object);
        }

        $this->assertSame("first test string", $test_objects[0]->test_string);
        $this->assertSame(1, $test_objects[0]->test_int);

        $this->assertSame("second test string", $test_objects[1]->test_string);
        $this->assertSame(2, $test_objects[1]->test_int);
    }

    public function test_fetchAllAsObject_exception_on_prepare()
    {
        $pdo_simple = $this->injector->make(PdoSimple::class);

        $this->expectException(PdoSimpleException::class);
        $this->expectExceptionMessageMatchesTemplateString(
            PdoSimpleWithPreviousException::INVALID_SQL
        );
        $pdo_simple->fetchAllAsObject(
            "invalid sql",
            [],
            PdoSimpleTestObject::class
        );
    }

    public function test_fetchAllAsObject_exception_on_execute()
    {
        $pdo_simple = $this->injector->make(PdoSimple::class);

        $sql = <<< SQL
select 
  id,
  test_string, 
  test_int,
  created_at
from
  pdo_simple_test
where
  test_string like :foo
SQL;

        $this->expectException(PdoSimpleException::class);
        $this->expectExceptionMessageMatchesTemplateString(
            PdoSimpleWithPreviousException::ERROR_EXECUTING_STATEMENT
        );

        $pdo_simple->fetchAllAsObject(
            $sql,
            [], // missing param :foo
            PdoSimpleTestObject::class
        );
    }

    public function test_fetchAllAsScalar()
    {
        $pdo_simple = $this->injector->make(PdoSimple::class);

        $sql = <<< SQL
select  
    test_string
from
  pdo_simple_test
SQL;
        $result = $pdo_simple->fetchAllRowsAsScalar($sql, []);
        $this->assertIsArray($result);
    }

    public function test_fetchAllAsScalar_too_many_columns()
    {
        $pdo_simple = $this->injector->make(PdoSimple::class);
        $sql = <<< SQL
select
    id,
    test_string
from
  pdo_simple_test
SQL;
        $this->expectException(PdoSimpleException::class);
        $this->expectExceptionMessageMatchesTemplateString(PdoSimpleException::TOO_MANY_COLUMNS_MESSAGE);

        $pdo_simple->fetchAllRowsAsScalar($sql, []);
    }

    public function test_fetchAllAsData()
    {
        $pdo_simple = $this->injector->make(PdoSimple::class);
        $sql = pdo_simple_test::SELECT;

        $result = $pdo_simple->fetchAllAsData($sql, []);
        $this->assertIsArray($result);
        $this->assertGreaterThan(0, count($result));

        $first_result = $result[0];

        $this->assertArrayHasKey('id', $first_result);
        $this->assertArrayHasKey('test_string', $first_result);
        $this->assertArrayHasKey('test_int', $first_result);
    }


    public function test_fetchOneAsObject()
    {
        $pdo_simple = $this->injector->make(PdoSimple::class);
        $sql = pdo_simple_test::SELECT;

        $result = $pdo_simple->fetchOneAsObject($sql, [], PdoSimpleTestObjectProperties::class);
        $this->assertInstanceOf(PdoSimpleTestObjectProperties::class, $result);

        // "second test string"
    }

    public function test_fetchOneAsObject_errors()
    {
        $pdo_simple = $this->injector->make(PdoSimple::class);
        $sql = pdo_simple_test::SELECT;

        $sql .= " where test_string = 'does not exist'";

        $this->expectException(RowNotFoundException::class);
        $pdo_simple->fetchOneAsObject($sql, [], PdoSimpleTestObjectProperties::class);
    }


    public function test_fetchOneAsDataOrNull()
    {
        // Setup
        $pdo_simple = $this->injector->make(PdoSimple::class);
        $sql = pdo_simple_test::SELECT;
        $result = $pdo_simple->fetchOneAsDataOrNull($sql, []);

        // Assertions
        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('test_string', $result);
        $this->assertArrayHasKey('test_int', $result);

        // Setup
        $pdo_simple = $this->injector->make(PdoSimple::class);
        $sql = pdo_simple_test::SELECT;
        $sql .= " where test_string = 'does not exist'";
        $result = $pdo_simple->fetchOneAsDataOrNull($sql, []);

        // Assertions
        $this->assertNull($result);
    }

    public function test_fetchOneAsObjectOrNull()
    {
        // Setup
        $pdo_simple = $this->injector->make(PdoSimple::class);
        $sql = pdo_simple_test::SELECT;
        $result = $pdo_simple->fetchOneAsObjectOrNull($sql, [], PdoSimpleTestObjectProperties::class);

        // Assertions
        $this->assertInstanceOf(PdoSimpleTestObjectProperties::class, $result);
//        $this->assertArrayHasKey('id', $result->id);
//        $this->assertArrayHasKey('test_string', $result->test_string);
//        $this->assertArrayHasKey('test_int', $result->test_int);

        // Setup
        $pdo_simple = $this->injector->make(PdoSimple::class);
        $sql = pdo_simple_test::SELECT;
        $sql .= " where test_string = 'does not exist'";
        $result = $pdo_simple->fetchOneAsObjectOrNull($sql, [], PdoSimpleTestObjectProperties::class);

        // Assertions
        $this->assertNull($result);
    }


//fetchOneAsObjectOrNull => PDO::FETCH_CLASS - writes properties
//
//fetchOneAsObjectOrNullConstructor => FETCH_ASSOC - uses constructor

    public function test_fetchAllAsObjectConstructor()
    {
        // Setup
        $pdo_simple = $this->injector->make(PdoSimple::class);
        $sql = pdo_simple_test::SELECT;
        $result = $pdo_simple->fetchAllAsObjectConstructor($sql, [], PdoSimpleTestObjectConstructor::class);

        // Assertions
        $this->assertIsArray($result);
        $object = $result[0];
        $this->assertInstanceOf(PdoSimpleTestObjectConstructor::class, $object);


        // Setup
        $pdo_simple = $this->injector->make(PdoSimple::class);
        $sql2 = pdo_simple_test::SELECT;
        $sql2 .= " where test_string = 'does not exist'";
        $result = $pdo_simple->fetchAllAsObjectConstructor($sql2, [], PdoSimpleTestObjectConstructor::class);

        // Assertions
        $this->assertEmpty($result);
    }


    public function test_fetchOneAsObjectOrNullConstructor()
    {
        // Setup
        $pdo_simple = $this->injector->make(PdoSimple::class);
        $sql = pdo_simple_test::SELECT;
        $result = $pdo_simple->fetchOneAsObjectOrNullConstructor($sql, [], PdoSimpleTestObjectConstructor::class);

        // Assertions
        $this->assertInstanceOf(PdoSimpleTestObjectConstructor::class, $result);

        // Setup
        $pdo_simple = $this->injector->make(PdoSimple::class);
        $sql2 = pdo_simple_test::SELECT;
        $sql2 .= " where test_string = 'does not exist'";
        $result = $pdo_simple->fetchOneAsObjectOrNullConstructor($sql2, [], PdoSimpleTestObjectConstructor::class);

        // Assertions
        $this->assertNull($result);
    }



    public function test_insert()
    {
        $pdo_simple = $this->injector->make(PdoSimple::class);
        $sql = <<< SQL
insert into pdo_simple_test (
    test_string,
    test_int
)
values (
    :test_string,
    :test_int
)
SQL;

        $test_string = $this->getTestString();
        $params = [
            ':test_string' => $test_string,
            ':test_int' => 1234,
        ];

        $insert_id = $pdo_simple->insert($sql, $params);


        $test_string = $this->getTestString();
        $params = [
            ':test_string' => $test_string,
            ':test_int' => 1234,
        ];

        $row_count = $pdo_simple->execute($sql, $params);
        $this->assertSame(1, $row_count);
    }
}
