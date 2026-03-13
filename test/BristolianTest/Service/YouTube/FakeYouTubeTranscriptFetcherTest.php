<?php

declare(strict_types=1);

namespace BristolianTest\Service\YouTube;

use Bristolian\Service\YouTube\FakeYouTubeTranscriptFetcher;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class FakeYouTubeTranscriptFetcherTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\YouTube\FakeYouTubeTranscriptFetcher::fetchAsVtt
     */
    public function test_fetchAsVtt_returns_default_when_no_transcript_added(): void
    {
        $fetcher = new FakeYouTubeTranscriptFetcher();
        [$vtt, $language] = $fetcher->fetchAsVtt('unknown_video_id');
        $this->assertStringContainsString('WEBVTT', $vtt);
        $this->assertStringContainsString('(No captions)', $vtt);
        $this->assertNull($language);
    }

    /**
     * @covers \Bristolian\Service\YouTube\FakeYouTubeTranscriptFetcher::addTranscript
     * @covers \Bristolian\Service\YouTube\FakeYouTubeTranscriptFetcher::fetchAsVtt
     */
    public function test_fetchAsVtt_returns_added_transcript(): void
    {
        $fetcher = new FakeYouTubeTranscriptFetcher();
        $vttContent = "WEBVTT\n\n00:00:00.000 --> 00:00:02.000\nHello world\n";
        $fetcher->addTranscript('video123', $vttContent, 'en');

        [$vtt, $language] = $fetcher->fetchAsVtt('video123');

        $this->assertSame($vttContent, $vtt);
        $this->assertSame('en', $language);
    }

    /**
     * @covers \Bristolian\Service\YouTube\FakeYouTubeTranscriptFetcher::addTranscript
     * @covers \Bristolian\Service\YouTube\FakeYouTubeTranscriptFetcher::fetchAsVtt
     */
    public function test_addTranscript_with_null_language(): void
    {
        $fetcher = new FakeYouTubeTranscriptFetcher();
        $fetcher->addTranscript('vid2', 'WEBVTT\n\n', null);

        [, $language] = $fetcher->fetchAsVtt('vid2');
        $this->assertNull($language);
    }
}
