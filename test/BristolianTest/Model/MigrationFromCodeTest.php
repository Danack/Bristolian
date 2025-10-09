<?php

namespace BristolianTest\Model;

use BristolianTest\BaseTestCase;
use Bristolian\Model\MigrationFromCode;

/**
 * @coversNothing
 */
class MigrationFromCodeTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\MigrationFromCode
     */
    public function testConstruct()
    {
        $id = 1;
        $description = 'Test migration';
        $queriesToRun = [
            'CREATE TABLE test (id INT)',
            'INSERT INTO test VALUES (1)'
        ];

        $migration = new MigrationFromCode($id, $description, $queriesToRun);

        $this->assertSame($id, $migration->id);
        $this->assertSame($description, $migration->description);
        $this->assertSame($queriesToRun, $migration->queries_to_run);
        $this->assertIsArray($migration->queries_to_run);
        $this->assertCount(2, $migration->queries_to_run);
    }
}

