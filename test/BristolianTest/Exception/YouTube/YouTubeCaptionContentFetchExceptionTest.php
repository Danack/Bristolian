<?php

declare(strict_types=1);

namespace BristolianTest\Exception\YouTube;

use Bristolian\Exception\BristolianException;
use Bristolian\Exception\YouTube\YouTubeCaptionContentFetchException;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Exception\YouTube\YouTubeCaptionContentFetchException
 */
class YouTubeCaptionContentFetchExceptionTest extends BaseTestCase
{
    public function test_fromUrlFailure_returns_exception_with_message_and_previous(): void
    {
        $previous = new \RuntimeException('404');
        $e = YouTubeCaptionContentFetchException::fromUrlFailure('404', $previous);
        $this->assertInstanceOf(YouTubeCaptionContentFetchException::class, $e);
        $this->assertInstanceOf(BristolianException::class, $e);
        $this->assertStringContainsString('Failed to fetch caption content', $e->getMessage());
        $this->assertStringContainsString('404', $e->getMessage());
        $this->assertSame($previous, $e->getPrevious());
    }

    public function test_fromUrlFailure_accepts_null_previous(): void
    {
        $e = YouTubeCaptionContentFetchException::fromUrlFailure('Error', null);
        $this->assertNull($e->getPrevious());
    }
}
