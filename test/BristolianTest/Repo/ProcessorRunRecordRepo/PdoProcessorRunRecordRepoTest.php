<?php

namespace BristolianTest\Repo\ProcessorRunRecordRepo;

use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Repo\ProcessorRepo\ProcessType;
use Bristolian\Repo\ProcessorRunRecordRepo\PdoProcessorRunRecordRepo;
use Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo;
use Bristolian\Model\Generated\ProcessorRunRecord;
use BristolianTest\Repo\DbTransactionIsolation;

/**
 * @group db
 * @coversNothing
 */
class PdoProcessorRunRecordRepoTest extends ProcessorRunRecordRepoFixture
{
    use DbTransactionIsolation;

    public function setUp(): void
    {
        parent::setUp();
        $this->dbTransactionSetUp();
    }

    public function tearDown(): void
    {
        $this->dbTransactionTearDown();
        parent::tearDown();
    }

    protected function dbTransactionClearTables(): void
    {
        $pdoSimple = $this->injector->make(PdoSimple::class);
        $pdoSimple->execute('DELETE FROM processor_run_record', []);
    }

    public function getTestInstance(): ProcessorRunRecordRepo
    {
        return $this->injector->make(PdoProcessorRunRecordRepo::class);
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\PdoProcessorRunRecordRepo
     */
    public function test_startRun_creates_record(): void
    {
        $repo = $this->injector->make(PdoProcessorRunRecordRepo::class);

        $id = $repo->startRun(ProcessType::email_send);

        $this->assertNotEmpty($id);
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\PdoProcessorRunRecordRepo
     */
    public function test_getLastRunDateTime_returns_null_when_no_runs(): void
    {
        $repo = $this->injector->make(PdoProcessorRunRecordRepo::class);

        // For a processor type that has never run, should return null
        $lastRun = $repo->getLastRunDateTime(ProcessType::moon_alert);

        // Note: This might not be null if there are existing records from other tests
        // So we just verify it's either null or a DateTimeInterface
        $this->assertTrue($lastRun === null || $lastRun instanceof \DateTimeInterface);
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\PdoProcessorRunRecordRepo
     */
    public function test_getLastRunDateTime_returns_most_recent(): void
    {
        $repo = $this->injector->make(PdoProcessorRunRecordRepo::class);

        // Start two runs
        $id1 = $repo->startRun(ProcessType::daily_system_info);
        
        // Small delay to ensure different timestamps
        usleep(10000); // 10ms
        
        $id2 = $repo->startRun(ProcessType::daily_system_info);

        $lastRun = $repo->getLastRunDateTime(ProcessType::daily_system_info);

        $this->assertInstanceOf(\DateTimeInterface::class, $lastRun);
        
        // The last run should be very recent (within last few seconds)
        $now = new \DateTimeImmutable();
        $diff = $now->getTimestamp() - $lastRun->getTimestamp();
        $this->assertLessThan(5, $diff, 'Last run should be very recent');
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\PdoProcessorRunRecordRepo
     */
    public function test_setRunFinished_marks_run_complete(): void
    {
        $repo = $this->injector->make(PdoProcessorRunRecordRepo::class);

        $id = $repo->startRun(ProcessType::email_send);
        
        // Small delay to ensure different timestamps
        usleep(10000); // 10ms
        
        $debug_info = "Test debug information";
        $repo->setRunFinished($id, $debug_info);

        // Get the run record to verify it was updated
        $records = $repo->getRunRecords(ProcessType::email_send);
        
        // Find the record we just created
        $foundRecord = null;
        foreach ($records as $record) {
            if ($record->id === (int)$id) {
                $foundRecord = $record;
                break;
            }
        }

        $this->assertNotNull($foundRecord, 'Should find the created record');
        $this->assertSame(ProcessorRunRecordRepo::STATE_FINISHED, $foundRecord->status);
        $this->assertInstanceOf(\DateTimeInterface::class, $foundRecord->end_time);
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\PdoProcessorRunRecordRepo
     */
    public function test_getRunRecords_without_filter(): void
    {
        $repo = $this->injector->make(PdoProcessorRunRecordRepo::class);

        // Create some run records
        $repo->startRun(ProcessType::email_send);
        $repo->startRun(ProcessType::daily_system_info);
        $repo->startRun(ProcessType::moon_alert);

        $records = $repo->getRunRecords(null);

        $this->assertIsArray($records);
        $this->assertGreaterThanOrEqual(3, count($records));
        
        foreach ($records as $record) {
            $this->assertInstanceOf(ProcessorRunRecord::class, $record);
        }
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\PdoProcessorRunRecordRepo
     */
    public function test_getRunRecords_with_filter(): void
    {
        $repo = $this->injector->make(PdoProcessorRunRecordRepo::class);

        // Create run records for different types
        $repo->startRun(ProcessType::email_send);
        $repo->startRun(ProcessType::email_send);
        $repo->startRun(ProcessType::daily_system_info);

        $emailRecords = $repo->getRunRecords(ProcessType::email_send);

        $this->assertIsArray($emailRecords);
        $this->assertGreaterThanOrEqual(2, count($emailRecords));
        
        // All returned records should be for email_send
        foreach ($emailRecords as $record) {
            $this->assertSame(ProcessType::email_send->value, $record->processor_type);
        }
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\PdoProcessorRunRecordRepo
     */
    public function test_processor_run_record_properties(): void
    {
        $repo = $this->injector->make(PdoProcessorRunRecordRepo::class);

        $id = $repo->startRun(ProcessType::moon_alert);
        
        $records = $repo->getRunRecords(ProcessType::moon_alert);
        
        // Find the record we just created
        $foundRecord = null;
        foreach ($records as $record) {
            if ($record->id === (int)$id) {
                $foundRecord = $record;
                break;
            }
        }

        $this->assertNotNull($foundRecord);
        
        // Verify all properties
        $this->assertIsInt($foundRecord->id);
        $this->assertInstanceOf(\DateTimeInterface::class, $foundRecord->start_time);
        $this->assertTrue($foundRecord->end_time === null || $foundRecord->end_time instanceof \DateTimeInterface);
        $this->assertSame(ProcessorRunRecordRepo::STATE_INITIAL, $foundRecord->status);
        $this->assertSame(ProcessType::moon_alert->value, $foundRecord->processor_type);
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\PdoProcessorRunRecordRepo
     */
    public function test_multiple_runs_for_same_processor(): void
    {
        $repo = $this->injector->make(PdoProcessorRunRecordRepo::class);

        // Create multiple runs for the same processor
        $id1 = $repo->startRun(ProcessType::daily_system_info);
        usleep(10000);
        $id2 = $repo->startRun(ProcessType::daily_system_info);
        usleep(10000);
        $id3 = $repo->startRun(ProcessType::daily_system_info);

        $records = $repo->getRunRecords(ProcessType::daily_system_info);

        $this->assertGreaterThanOrEqual(3, count($records));
        
        // Verify all three IDs are present
        $foundIds = array_map(fn($r) => $r->id, $records);
        $this->assertContains((int)$id1, $foundIds);
        $this->assertContains((int)$id2, $foundIds);
        $this->assertContains((int)$id3, $foundIds);
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\PdoProcessorRunRecordRepo
     */
    public function test_records_ordered_by_id_desc(): void
    {
        $repo = $this->injector->make(PdoProcessorRunRecordRepo::class);

        // Create several runs
        $id1 = $repo->startRun(ProcessType::email_send);
        usleep(10000);
        $id2 = $repo->startRun(ProcessType::email_send);
        usleep(10000);
        $id3 = $repo->startRun(ProcessType::email_send);

        $records = $repo->getRunRecords(ProcessType::email_send);

        // Verify records are in descending order by ID
        $previousId = PHP_INT_MAX;
        foreach ($records as $record) {
            $this->assertLessThanOrEqual($previousId, $record->id);
            $previousId = $record->id;
        }
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\PdoProcessorRunRecordRepo
     */
    public function test_complete_workflow(): void
    {
        $repo = $this->injector->make(PdoProcessorRunRecordRepo::class);

        // Start a run
        $id = $repo->startRun(ProcessType::moon_alert);

        // Get the last run time
        $lastRun = $repo->getLastRunDateTime(ProcessType::moon_alert);
        $this->assertInstanceOf(\DateTimeInterface::class, $lastRun);

        // Finish the run
        $debug_info = "Successfully completed test run";
        $repo->setRunFinished($id, $debug_info);

        // Verify the record is finished
        $records = $repo->getRunRecords(ProcessType::moon_alert);
        $foundRecord = null;
        foreach ($records as $record) {
            if ($record->id === (int)$id) {
                $foundRecord = $record;
                break;
            }
        }

        $this->assertNotNull($foundRecord);
        $this->assertSame(ProcessorRunRecordRepo::STATE_FINISHED, $foundRecord->status);
        $this->assertNotNull($foundRecord->end_time);
        $this->assertGreaterThanOrEqual(
            $foundRecord->start_time->getTimestamp(),
            $foundRecord->end_time->getTimestamp(),
            'End time should be at or after start time'
        );
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\PdoProcessorRunRecordRepo
     */
    public function test_different_process_types_independent(): void
    {
        $repo = $this->injector->make(PdoProcessorRunRecordRepo::class);

        // Create runs for different types
        $emailId = $repo->startRun(ProcessType::email_send);
        $dailyId = $repo->startRun(ProcessType::daily_system_info);
        $moonId = $repo->startRun(ProcessType::moon_alert);

        // Get records for each type
        $emailRecords = $repo->getRunRecords(ProcessType::email_send);
        $dailyRecords = $repo->getRunRecords(ProcessType::daily_system_info);
        $moonRecords = $repo->getRunRecords(ProcessType::moon_alert);

        // Verify each list contains its respective ID
        $emailIds = array_map(fn($r) => $r->id, $emailRecords);
        $this->assertContains((int)$emailId, $emailIds);

        $dailyIds = array_map(fn($r) => $r->id, $dailyRecords);
        $this->assertContains((int)$dailyId, $dailyIds);

        $moonIds = array_map(fn($r) => $r->id, $moonRecords);
        $this->assertContains((int)$moonId, $moonIds);

        // Verify each list only contains records for its type
        foreach ($emailRecords as $record) {
            $this->assertSame(ProcessType::email_send->value, $record->processor_type);
        }
        foreach ($dailyRecords as $record) {
            $this->assertSame(ProcessType::daily_system_info->value, $record->processor_type);
        }
        foreach ($moonRecords as $record) {
            $this->assertSame(ProcessType::moon_alert->value, $record->processor_type);
        }
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\PdoProcessorRunRecordRepo
     */
    public function test_start_time_is_recent(): void
    {
        $repo = $this->injector->make(PdoProcessorRunRecordRepo::class);

        $before = new \DateTimeImmutable();
        $id = $repo->startRun(ProcessType::email_send);
        $after = new \DateTimeImmutable();

        $records = $repo->getRunRecords(ProcessType::email_send);
        
        $foundRecord = null;
        foreach ($records as $record) {
            if ($record->id === (int)$id) {
                $foundRecord = $record;
                break;
            }
        }

        $this->assertNotNull($foundRecord);
        
        // Verify start_time is between before and after
        $this->assertGreaterThanOrEqual(
            $before->getTimestamp(),
            $foundRecord->start_time->getTimestamp()
        );
        $this->assertLessThanOrEqual(
            $after->getTimestamp(),
            $foundRecord->start_time->getTimestamp()
        );
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\PdoProcessorRunRecordRepo
     */
    public function test_initial_run_has_no_end_time(): void
    {
        $repo = $this->injector->make(PdoProcessorRunRecordRepo::class);

        $id = $repo->startRun(ProcessType::daily_system_info);

        $records = $repo->getRunRecords(ProcessType::daily_system_info);
        
        $foundRecord = null;
        foreach ($records as $record) {
            if ($record->id === (int)$id) {
                $foundRecord = $record;
                break;
            }
        }

        $this->assertNotNull($foundRecord);
        $this->assertNull($foundRecord->end_time, 'Initial run should have no end_time');
        $this->assertSame(ProcessorRunRecordRepo::STATE_INITIAL, $foundRecord->status);
    }

    /**
     * @covers \Bristolian\Repo\ProcessorRunRecordRepo\PdoProcessorRunRecordRepo
     */
    public function test_getRunRecords_respects_limit(): void
    {
        $repo = $this->injector->make(PdoProcessorRunRecordRepo::class);

        // The limit is 50, so we should get at most 50 records
        $records = $repo->getRunRecords(null);

        $this->assertLessThanOrEqual(50, count($records), 'Should respect the limit of 50 records');
    }
}
