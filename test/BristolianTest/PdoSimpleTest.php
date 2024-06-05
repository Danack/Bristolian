<?php

namespace BristolianTest;

use Bristolian\CSPViolation\RedisCSPViolationStorage;
use Bristolian\PdoSimple;
use BristolianTest\PdoSimpleTestObject;

/**
 * @coversNothing
 *
 *
 */
class  PdoSimpleTest extends BaseTestCase
{

    public function testWorks()
    {

        $injector = createTestInjector();
        $pdo_simple = $injector->make(PdoSimple::class);

        // Todo - generate this sql...

        $sql = <<< SQL
select 
  id,
  test_string, 
  test_int,
  created_at
from
  pdo_simple_test
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
}
