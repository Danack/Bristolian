<?php

declare(strict_types=1);

namespace BristolianTest\Cache;

use Bristolian\Cache\RequestTableAccessRecorder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Bristolian\Cache\RequestTableAccessRecorder
 */
class RequestTableAccessRecorderTest extends TestCase
{
    public function testRecordTablesReadSingle(): void
    {
        $recorder = new RequestTableAccessRecorder();
        $recorder->recordTablesRead(['users']);

        $tags = $recorder->getTagsForResponse();
        $this->assertSame('table:users', $tags);
    }

    public function testRecordTablesReadMultiple(): void
    {
        $recorder = new RequestTableAccessRecorder();
        $recorder->recordTablesRead(['users', 'rooms']);

        $tags = $recorder->getTagsForResponse();
        $this->assertStringContainsString('table:users', $tags);
        $this->assertStringContainsString('table:rooms', $tags);
    }

    public function testRecordTablesReadDeduplicates(): void
    {
        $recorder = new RequestTableAccessRecorder();
        $recorder->recordTablesRead(['users']);
        $recorder->recordTablesRead(['users', 'rooms']);

        $tags = $recorder->getTagsForResponse();
        $this->assertSame('table:users,table:rooms', $tags);
    }

    public function testGetTagsForResponseEmptyWhenNoReads(): void
    {
        $recorder = new RequestTableAccessRecorder();
        $this->assertSame('', $recorder->getTagsForResponse());
    }

    public function testGetTagsForResponseIgnoresWrites(): void
    {
        $recorder = new RequestTableAccessRecorder();
        $recorder->recordTablesWritten(['users']);

        $this->assertSame('', $recorder->getTagsForResponse());
    }

    public function testRecordTablesWrittenSingle(): void
    {
        $recorder = new RequestTableAccessRecorder();
        $recorder->recordTablesWritten(['users']);

        $tablesWritten = $recorder->getTablesWritten();
        $this->assertSame(['users'], $tablesWritten);
    }

    public function testRecordTablesWrittenDeduplicates(): void
    {
        $recorder = new RequestTableAccessRecorder();
        $recorder->recordTablesWritten(['users']);
        $recorder->recordTablesWritten(['users', 'rooms']);

        $tablesWritten = $recorder->getTablesWritten();
        $this->assertCount(2, $tablesWritten);
        $this->assertContains('users', $tablesWritten);
        $this->assertContains('rooms', $tablesWritten);
    }

    public function testGetTablesWrittenEmptyInitially(): void
    {
        $recorder = new RequestTableAccessRecorder();
        $this->assertSame([], $recorder->getTablesWritten());
    }

    public function testClearResetsReadsAndWrites(): void
    {
        $recorder = new RequestTableAccessRecorder();
        $recorder->recordTablesRead(['users']);
        $recorder->recordTablesWritten(['rooms']);

        $recorder->clear();

        $this->assertSame('', $recorder->getTagsForResponse());
        $this->assertSame([], $recorder->getTablesWritten());
    }

    public function testRecordTablesReadAccumulatesAcrossMultipleCalls(): void
    {
        $recorder = new RequestTableAccessRecorder();
        $recorder->recordTablesRead(['users']);
        $recorder->recordTablesRead(['rooms']);
        $recorder->recordTablesRead(['links']);

        $tags = $recorder->getTagsForResponse();
        $this->assertSame('table:users,table:rooms,table:links', $tags);
    }

    public function testRecordTablesWrittenAccumulatesAcrossMultipleCalls(): void
    {
        $recorder = new RequestTableAccessRecorder();
        $recorder->recordTablesWritten(['users']);
        $recorder->recordTablesWritten(['rooms']);

        $tablesWritten = $recorder->getTablesWritten();
        $this->assertCount(2, $tablesWritten);
        $this->assertContains('users', $tablesWritten);
        $this->assertContains('rooms', $tablesWritten);
    }
}
