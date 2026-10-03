<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\ProcessorRunRecordRepo;

use Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo;
use Bristolian\Repo\ProcessorRunRecordRepo\FakeProcessorRunRecordRepo;
use Bristolian\Repo\ProcessorRepo\ProcessType;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @group standard_repo
 */

#[CoversClass(FakeProcessorRunRecordRepo::class)]
class FakeProcessorRunRecordRepoTest extends ProcessorRunRecordRepoFixture
{
    public function getTestInstance(): ProcessorRunRecordRepo
    {
        return new FakeProcessorRunRecordRepo();
    }

    public function test_getLastRunDateTime_returns_start_time_when_records_exist(): void
    {
        $repo = new FakeProcessorRunRecordRepo();
        $repo->startRun(ProcessType::email_send);
        $lastRun = $repo->getLastRunDateTime(ProcessType::email_send);
        $this->assertNotNull($lastRun);
    }

    public function test_setRunFinished_and_getRunRecords(): void
    {
        $repo = new FakeProcessorRunRecordRepo();
        $id = $repo->startRun(ProcessType::meme_ocr);
        $repo->setRunFinished($id, 'debug');
        $records = $repo->getRunRecords(ProcessType::meme_ocr);
        $this->assertCount(1, $records);
        $this->assertSame('debug', $records[0]->debug_info);
    }

    public function test_getRunRecords_returns_records_sorted_by_id_desc(): void
    {
        $repo = new FakeProcessorRunRecordRepo();
        $repo->startRun(ProcessType::email_send);
        $repo->startRun(ProcessType::email_send);
        $records = $repo->getRunRecords(ProcessType::email_send);
        $this->assertCount(2, $records);
        $this->assertGreaterThan($records[1]->id, $records[0]->id);
    }
}
