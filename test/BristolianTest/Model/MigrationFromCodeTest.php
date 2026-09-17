<?php

namespace BristolianTest\Model;

use Bristolian\Model\Types\MigrationFromCode;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\Model\Types\MigrationFromCode::class)]

class MigrationFromCodeTest extends BaseTestCase
{
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
