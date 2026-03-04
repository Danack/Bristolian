<?php

declare(strict_types=1);

namespace BristolianTest\Exception\YouTube;

use Bristolian\Exception\BristolianException;
use Bristolian\Exception\YouTube\YouTubeWatchPageFetchException;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Exception\YouTube\YouTubeWatchPageFetchException
 */
class YouTubeWatchPageFetchExceptionTest extends BaseTestCase
{
    public function test_fromUrlFailure_returns_exception_with_message_and_previous(): void
    {
        $previous = new \RuntimeException('Not found');
        $e = YouTubeWatchPageFetchException::fromUrlFailure('Not found', $previous);
        $this->assertInstanceOf(YouTubeWatchPageFetchException::class, $e);
        $this->assertInstanceOf(BristolianException::class, $e);
        $this->assertStringContainsString('Failed to load YouTube watch page', $e->getMessage());
        $this->assertStringContainsString('Not found', $e->getMessage());
        $this->assertSame($previous, $e->getPrevious());
    }

    public function test_fromUrlFailure_accepts_null_previous(): void
    {
        $e = YouTubeWatchPageFetchException::fromUrlFailure('Error', null);
        $this->assertNull($e->getPrevious());
    }
}
