<?php

namespace BristolianTest\Model;

use BristolianTest\BaseTestCase;
use RunTimeRecord;

/**
 * @coversNothing
 */
class RunTimeRecordTest extends BaseTestCase
{
    /**
     * @covers \RunTimeRecord
     */
    public function testConstruct()
    {
        $this->markTestSkipped("This code is dead");

        $id = 'runtime-123';
        $endTime = new \DateTimeImmutable();
        $startTime = new \DateTimeImmutable('-1 hour');
        $status = 'completed';
        $task = 'test_task';

        $record = new RunTimeRecord($id, $endTime, $startTime, $status, $task);

        $this->assertSame($id, $record->id);
        $this->assertSame($endTime, $record->end_time);
        $this->assertSame($startTime, $record->start_time);
        $this->assertSame($status, $record->status);
        $this->assertSame($task, $record->task);
    }

    /**
     * @covers \RunTimeRecord
     */
    public function testConstructWithNullTimes()
    {
        $this->markTestSkipped("This code is dead");

        $record = new RunTimeRecord('id', null, null, 'pending', 'task');

        $this->assertNull($record->end_time);
        $this->assertNull($record->start_time);
    }
}

