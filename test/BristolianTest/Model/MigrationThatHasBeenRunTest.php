<?php

namespace BristolianTest\Model;

use Bristolian\Model\Types\MigrationThatHasBeenRun;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class MigrationThatHasBeenRunTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Types\MigrationThatHasBeenRun
     */
    public function testConstruct()
    {
        $id = 1;
        $description = 'Migration 1';
        $jsonEncodedQueries = '["CREATE TABLE test"]';
        $createdAt = new \DateTimeImmutable();

        $migration = new MigrationThatHasBeenRun(
            $id,
            $description,
            $jsonEncodedQueries,
            $createdAt
        );

        $this->assertSame($id, $migration->id);
        $this->assertSame($description, $migration->description);
        $this->assertSame($jsonEncodedQueries, $migration->json_encoded_queries);
        $this->assertSame($createdAt, $migration->created_at);
    }
}
