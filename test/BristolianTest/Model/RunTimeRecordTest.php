<?php

namespace BristolianTest\Model;

use BristolianTest\BaseTestCase;
use Bristolian\Model\RunTimeRecord;

/**
 * @coversNothing
 */
class RunTimeRecordTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\RunTimeRecord
     */
    public function testConstruct()
    {
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
     * @covers \Bristolian\Model\RunTimeRecord
     */
    public function testConstructWithNullTimes()
    {
        $record = new RunTimeRecord('id', null, null, 'pending', 'task');

        $this->assertNull($record->end_time);
        $this->assertNull($record->start_time);
    }
}

