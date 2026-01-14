<?php

namespace BristolianTest\Parameters;

use Bristolian\Model\Types\MigrationThatHasBeenRun;
use BristolianTest\BaseTestCase;
use Safe\DateTimeImmutable;

/**
 * @covers \Bristolian\Model\Types\MigrationThatHasBeenRun
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
