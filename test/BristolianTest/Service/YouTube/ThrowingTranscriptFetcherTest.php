<?php

declare(strict_types=1);

namespace BristolianTest\Service\YouTube;

use Bristolian\Exception\YouTube\YouTubeNoCaptionTracksException;
use Bristolian\Service\YouTube\ThrowingTranscriptFetcher;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class ThrowingTranscriptFetcherTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\YouTube\ThrowingTranscriptFetcher
     */
    function testWorks(): void
    {
        $id = "abcdef12345";
        $thrower = new ThrowingTranscriptFetcher();

        $this->expectException(YouTubeNoCaptionTracksException::class);
        $thrower->fetchAsVtt($id);
    }
}
