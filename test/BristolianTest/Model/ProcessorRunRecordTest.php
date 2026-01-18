<?php

namespace BristolianTest\Model;

use BristolianTest\BaseTestCase;

use Bristolian\Model\Generated\ProcessorRunRecord;

/**
 * @coversNothing
 */
class ProcessorRunRecordTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Generated\ProcessorRunRecord
     */
    public function testConstruct()
    {
        $id = 1;
        $startTime = new \DateTimeImmutable();
        $endTime = new \DateTimeImmutable('+1 hour');
        $status = 'completed';
        $debugInfo = 'Debug information';
        $processorType = 'email_processor';

        $record = new ProcessorRunRecord(
            $id,
            $processorType,
            $debugInfo,
            $startTime,
            $status,
            $endTime
        );

        $this->assertSame($id, $record->id);
        $this->assertSame($startTime, $record->start_time);
        $this->assertSame($endTime, $record->end_time);
        $this->assertSame($status, $record->status);
        $this->assertSame($debugInfo, $record->debug_info);
        $this->assertSame($processorType, $record->processor_type);
    }

    /**
     * @covers \Bristolian\Model\Generated\ProcessorRunRecord
     */
    public function testConstructWithNullEndTime()
    {
        $record = new ProcessorRunRecord(
            1,
            'test_processor',
            'Still processing',
            new \DateTimeImmutable(),
            'running',
            null,
        );

        $this->assertNull($record->end_time);
    }

    /**
     * @covers \Bristolian\Model\Generated\ProcessorRunRecord
     */
    public function testToArray()
    {
        $record = new ProcessorRunRecord(
            1,
            'test_processor',
            'Still processing',
            new \DateTimeImmutable(),
            'running',
            null,
        );

        $array = $record->toArray();
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('status', $array);
    }
}
