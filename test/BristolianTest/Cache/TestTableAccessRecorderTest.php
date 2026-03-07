<?php

declare(strict_types=1);

namespace BristolianTest\Cache;

use Bristolian\Cache\TestTableAccessRecorder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Bristolian\Cache\TestTableAccessRecorder
 */
class TestTableAccessRecorderTest extends TestCase
{
    public function testRecordedReadsInitiallyEmpty(): void
    {
        $recorder = new TestTableAccessRecorder();
        $this->assertSame([], $recorder->getRecordedReads());
    }

    public function testRecordedWritesInitiallyEmpty(): void
    {
        $recorder = new TestTableAccessRecorder();
        $this->assertSame([], $recorder->getRecordedWrites());
    }

    public function testRecordTablesReadStoresEachCallSeparately(): void
    {
        $recorder = new TestTableAccessRecorder();
        $recorder->recordTablesRead(['users']);
        $recorder->recordTablesRead(['rooms', 'links']);

        $reads = $recorder->getRecordedReads();
        $this->assertCount(2, $reads);
        $this->assertSame(['users'], $reads[0]);
        $this->assertSame(['rooms', 'links'], $reads[1]);
    }

    public function testRecordTablesWrittenStoresEachCallSeparately(): void
    {
        $recorder = new TestTableAccessRecorder();
        $recorder->recordTablesWritten(['users']);
        $recorder->recordTablesWritten(['rooms']);

        $writes = $recorder->getRecordedWrites();
        $this->assertCount(2, $writes);
        $this->assertSame(['users'], $writes[0]);
        $this->assertSame(['rooms'], $writes[1]);
    }

    public function testGetTagsForResponseDelegatesToInner(): void
    {
        $recorder = new TestTableAccessRecorder();
        $recorder->recordTablesRead(['users', 'rooms']);

        $tags = $recorder->getTagsForResponse();
        $this->assertStringContainsString('table:users', $tags);
        $this->assertStringContainsString('table:rooms', $tags);
    }

    public function testGetTablesWrittenDelegatesToInner(): void
    {
        $recorder = new TestTableAccessRecorder();
        $recorder->recordTablesWritten(['users', 'rooms']);

        $tablesWritten = $recorder->getTablesWritten();
        $this->assertCount(2, $tablesWritten);
        $this->assertContains('users', $tablesWritten);
        $this->assertContains('rooms', $tablesWritten);
    }

    public function testClearResetsEverything(): void
    {
        $recorder = new TestTableAccessRecorder();
        $recorder->recordTablesRead(['users']);
        $recorder->recordTablesWritten(['rooms']);

        $recorder->clear();

        $this->assertSame([], $recorder->getRecordedReads());
        $this->assertSame([], $recorder->getRecordedWrites());
        $this->assertSame('', $recorder->getTagsForResponse());
        $this->assertSame([], $recorder->getTablesWritten());
    }

    public function testRecordedReadsPreservesDuplicatesAcrossCalls(): void
    {
        $recorder = new TestTableAccessRecorder();
        $recorder->recordTablesRead(['users']);
        $recorder->recordTablesRead(['users']);

        $reads = $recorder->getRecordedReads();
        $this->assertCount(2, $reads);
        $this->assertSame(['users'], $reads[0]);
        $this->assertSame(['users'], $reads[1]);
    }

    public function testInnerDeduplicatesWhileOuterRecordsAll(): void
    {
        $recorder = new TestTableAccessRecorder();
        $recorder->recordTablesRead(['users']);
        $recorder->recordTablesRead(['users']);

        $reads = $recorder->getRecordedReads();
        $this->assertCount(2, $reads);

        $tags = $recorder->getTagsForResponse();
        $this->assertSame('table:users', $tags);
    }
}
