<?php

declare(strict_types=1);

namespace BristolianTest\Exception\YouTube;

use Bristolian\Exception\BristolianException;
use Bristolian\Exception\YouTube\YouTubeNoCaptionTracksException;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Exception\YouTube\YouTubeNoCaptionTracksException
 */
class YouTubeNoCaptionTracksExceptionTest extends BaseTestCase
{
    public function test_forVideo_returns_exception_with_expected_message(): void
    {
        $e = YouTubeNoCaptionTracksException::forVideo('abc123');
        $this->assertInstanceOf(YouTubeNoCaptionTracksException::class, $e);
        $this->assertInstanceOf(BristolianException::class, $e);
        $this->assertSame('No caption tracks found for this video', $e->getMessage());
    }
}
