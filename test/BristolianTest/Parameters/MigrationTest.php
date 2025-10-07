<?php

namespace BristolianTest\Parameters;

use Bristolian\Parameters\PropertyType\BasicDateTime;
use Bristolian\Parameters\PropertyType\BasicString;
use BristolianTest\BaseTestCase;
use Bristolian\Model\MigrationThatHasBeenRun;
use DataType\Create\CreateFromArray;
use Safe\DateTimeImmutable;
use VarMap\ArrayVarMap;
use function DataType\createArrayOfType;

/**
 * @covers \Bristolian\Model\MigrationThatHasBeenRun
 */
class MigrationTest extends BaseTestCase
{
    public function testWorks()
    {
        $id = 123;
        $description = 'This is some description.';
        $queries = 'This is meant to be some queries';
        $datetime = new DateTimeImmutable();

        $migration = new MigrationThatHasBeenRun(
            $id,
            $description,
            $queries,
            $datetime,
        );

        $this->assertSame($id, $migration->id);
        $this->assertSame($description, $migration->description);
        $this->assertSame($queries, $migration->json_encoded_queries);
        $this->assertSame($datetime->format("Y-m-d H:i:s"), $migration->created_at->format("Y-m-d H:i:s"));
    }
}
