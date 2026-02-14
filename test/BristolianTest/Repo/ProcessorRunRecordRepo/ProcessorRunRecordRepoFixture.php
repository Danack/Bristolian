<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\ProcessorRunRecordRepo;

use Bristolian\Model\Generated\ProcessorRunRecord;
use Bristolian\Repo\ProcessorRepo\ProcessType;
use Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo;
use BristolianTest\BaseTestCase;

/**
 * Abstract test class for ProcessorRunRecordRepo implementations.
 *
 * @coversNothing
 */
abstract class ProcessorRunRecordRepoFixture extends BaseTestCase
{
    /**
     * Get a test instance of the ProcessorRunRecordRepo implementation.
     *
     * @return ProcessorRunRecordRepo
     */
    abstract public function getTestInstance(): ProcessorRunRecordRepo;

    /**
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo::getLastRunDateTime
     */
    public function test_getLastRunDateTime_returns_null_when_no_runs(): void
    {
        $repo = $this->getTestInstance();

        $result = $repo->getLastRunDateTime(ProcessType::email_send);

        $this->assertNull($result);
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo::startRun
     */
    public function test_startRun_creates_record_and_returns_id(): void
    {
        $repo = $this->getTestInstance();

        $id = $repo->startRun(ProcessType::email_send);

        $this->assertNotEmpty($id);
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo::startRun
     */
    public function test_startRun_creates_multiple_records_with_different_ids(): void
    {
        $repo = $this->getTestInstance();

        $id1 = $repo->startRun(ProcessType::email_send);
        $id2 = $repo->startRun(ProcessType::meme_ocr);

        $this->assertNotSame($id1, $id2);
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo::getLastRunDateTime
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo::startRun
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo::getRunRecords
     */
    public function test_getLastRunDateTime_returns_most_recent_start_time(): void
    {
        $repo = $this->getTestInstance();

        $id1 = $repo->startRun(ProcessType::email_send);
        // Small delay to ensure different timestamps
        usleep(1000);
        $id2 = $repo->startRun(ProcessType::email_send);

        $lastRun = $repo->getLastRunDateTime(ProcessType::email_send);

        $this->assertNotNull($lastRun);
        $this->assertInstanceOf(\DateTimeInterface::class, $lastRun);

        // Should be the most recent run
        $records = $repo->getRunRecords(ProcessType::email_send);
        $this->assertCount(2, $records);
        $this->assertEquals($lastRun, $records[0]->start_time);
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo::setRunFinished
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo::startRun
     */
    public function test_setRunFinished_updates_record_status(): void
    {
        $repo = $this->getTestInstance();

        $id = $repo->startRun(ProcessType::email_send);

        // Should not throw exception
        $repo->setRunFinished($id, 'Test debug info');
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo::getRunRecords
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo::startRun
     */
    public function test_getRunRecords_returns_all_records_when_processType_is_null(): void
    {
        $repo = $this->getTestInstance();

        $repo->startRun(ProcessType::email_send);
        $repo->startRun(ProcessType::meme_ocr);
        $repo->startRun(ProcessType::daily_system_info);

        $records = $repo->getRunRecords(null);

        $this->assertCount(3, $records);
        $this->assertContainsOnlyInstancesOf(ProcessorRunRecord::class, $records);
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo::getRunRecords
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo::startRun
     */
    public function test_getRunRecords_filters_by_processType(): void
    {
        $repo = $this->getTestInstance();

        $repo->startRun(ProcessType::email_send);
        $repo->startRun(ProcessType::meme_ocr);
        $repo->startRun(ProcessType::email_send);

        $records = $repo->getRunRecords(ProcessType::email_send);

        $this->assertCount(2, $records);
        foreach ($records as $record) {
            $this->assertSame(ProcessType::email_send->value, $record->processor_type);
        }
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo::getRunRecords
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo::startRun
     */
    public function test_getRunRecords_returns_ordered_by_id_desc(): void
    {
        $repo = $this->getTestInstance();

        $id1 = $repo->startRun(ProcessType::email_send);
        $id2 = $repo->startRun(ProcessType::email_send);
        $id3 = $repo->startRun(ProcessType::email_send);

        $records = $repo->getRunRecords(ProcessType::email_send);

        $this->assertCount(3, $records);
        // Should be ordered by id desc (newest first)
        $this->assertGreaterThan((int)$records[1]->id, (int)$records[0]->id);
        $this->assertGreaterThan((int)$records[2]->id, (int)$records[1]->id);
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo::startRun
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo::getLastRunDateTime
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo::setRunFinished
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo::getRunRecords
     */
    public function test_full_lifecycle(): void
    {
        $repo = $this->getTestInstance();

        // Start a run
        $id = $repo->startRun(ProcessType::email_send);

        // Get last run datetime
        $lastRun = $repo->getLastRunDateTime(ProcessType::email_send);
        $this->assertNotNull($lastRun);

        // Finish the run
        $repo->setRunFinished($id, 'Completed successfully');

        // Get records
        $records = $repo->getRunRecords(ProcessType::email_send);
        $this->assertCount(1, $records);
        $this->assertSame(ProcessorRunRecordRepo::STATE_FINISHED, $records[0]->status);
        $this->assertNotNull($records[0]->end_time);
        $this->assertSame('Completed successfully', $records[0]->debug_info);
    }
}
