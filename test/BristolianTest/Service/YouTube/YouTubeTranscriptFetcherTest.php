<?php

declare(strict_types=1);

namespace BristolianTest\Service\YouTube;

use Bristolian\Exception\YouTube\YouTubeCaptionContentFetchException;
use Bristolian\Exception\YouTube\YouTubeNoCaptionTracksException;
use Bristolian\Exception\YouTube\YouTubeWatchPageFetchException;
use Bristolian\Service\YouTube\YouTubeTranscriptFetcher;
use BristolianTest\BaseTestCase;
use UrlFetcher\UrlFetcher;
use UrlFetcher\UrlNotOkException;

/**
 * @covers \Bristolian\Service\YouTube\YouTubeTranscriptFetcher
 */
class YouTubeTranscriptFetcherTest extends BaseTestCase
{
    /**
     * fetchAsVtt returns VTT and language when watch page has captionTracks and caption is json3.
     */
    public function test_fetchAsVtt_returns_vtt_and_language_for_json3_captions(): void
    {
        $videoId = 'dQw4w9WgXcQ';
        $watchUrl = 'https://www.youtube.com/watch?v=' . $videoId;
        $watchHtml = '{"captionTracks":[{"baseUrl":"https://www.youtube.com/api/timedtext?lang=en&v=' . $videoId . '"}]}';
        $captionUrlWithFmt = 'https://www.youtube.com/api/timedtext?lang=en&v=' . $videoId . '&fmt=json3';
        $json3 = '{"events":[{"tStartMs":0,"dDurationMs":1500,"segs":[{"utf8":"Hello "},{"utf8":"world"}]},{"tStartMs":2000,"dDurationMs":1000,"segs":[{"utf8":"Bye"}]}]}';

        $urlFetcher = new MapUrlFetcher([
            $watchUrl => $watchHtml,
            $captionUrlWithFmt => $json3,
        ]);
        $fetcher = new YouTubeTranscriptFetcher($urlFetcher);

        [$vtt, $language] = $fetcher->fetchAsVtt($videoId);

        $this->assertStringStartsWith('WEBVTT', $vtt);
        $this->assertStringContainsString('00:00:00.000 --> 00:00:01.500', $vtt);
        $this->assertStringContainsString('Hello world', $vtt);
        $this->assertStringContainsString('00:00:02.000 --> 00:00:03.000', $vtt);
        $this->assertStringContainsString('Bye', $vtt);
        $this->assertSame('en', $language);
    }

    /**
     * fetchAsVtt throws when watch page has no caption tracks.
     */
    public function test_fetchAsVtt_throws_when_no_caption_tracks(): void
    {
        $videoId = 'noCaptions';
        $watchUrl = 'https://www.youtube.com/watch?v=' . $videoId;
        $watchHtml = '<html><body>No captionTracks here</body></html>';

        $urlFetcher = new MapUrlFetcher([$watchUrl => $watchHtml]);
        $fetcher = new YouTubeTranscriptFetcher($urlFetcher);

        $this->expectException(YouTubeNoCaptionTracksException::class);
        $this->expectExceptionMessage('No caption tracks found for this video');
        $fetcher->fetchAsVtt($videoId);
    }

    /**
     * fetchAsVtt returns VTT when caption content is srv1 XML (fallback path).
     */
    public function test_fetchAsVtt_returns_vtt_for_srv1_xml_captions(): void
    {
        $videoId = 'xmlVideo';
        $watchUrl = 'https://www.youtube.com/watch?v=' . $videoId;
        $watchHtml = '{"captionTracks":[{"baseUrl":"https://www.youtube.com/api/timedtext?lang=de&v=' . $videoId . '"}]}';
        $captionUrlWithFmt = 'https://www.youtube.com/api/timedtext?lang=de&v=' . $videoId . '&fmt=json3';
        $captionUrlRaw = 'https://www.youtube.com/api/timedtext?lang=de&v=' . $videoId;
        $xml = '<?xml version="1.0"?><transcript><text start="0" dur="1.5">Hallo</text><text start="2" dur="1">Welt</text></transcript>';

        // First request (with fmt=json3) throws so fetcher falls back to raw URL
        $urlFetcher = new MapUrlFetcher([
            $watchUrl => $watchHtml,
            $captionUrlWithFmt => null,
            $captionUrlRaw => $xml,
        ]);
        $fetcher = new YouTubeTranscriptFetcher($urlFetcher);

        [$vtt, $language] = $fetcher->fetchAsVtt($videoId);

        $this->assertStringStartsWith('WEBVTT', $vtt);
        $this->assertStringContainsString('00:00:00.000 --> 00:00:01.500', $vtt);
        $this->assertStringContainsString('Hallo', $vtt);
        $this->assertStringContainsString('00:00:02.000 --> 00:00:03.000', $vtt);
        $this->assertStringContainsString('Welt', $vtt);
        $this->assertSame('de', $language);
    }

    /**
     * fetchAsVtt throws when watch page fetch fails (UrlNotOkException).
     */
    public function test_fetchAsVtt_throws_when_watch_page_fetch_fails(): void
    {
        $videoId = 'failWatch';
        $watchUrl = 'https://www.youtube.com/watch?v=' . $videoId;

        $urlFetcher = new MapUrlFetcher([$watchUrl => null]);
        $fetcher = new YouTubeTranscriptFetcher($urlFetcher);

        $this->expectException(YouTubeWatchPageFetchException::class);
        $this->expectExceptionMessage('Failed to load YouTube watch page');
        $fetcher->fetchAsVtt($videoId);
    }

    /**
     * fetchAsVtt throws when both json3 and raw caption URL fail.
     */
    public function test_fetchAsVtt_throws_when_caption_fetch_fails(): void
    {
        $videoId = 'failCaption';
        $watchUrl = 'https://www.youtube.com/watch?v=' . $videoId;
        $watchHtml = '{"captionTracks":[{"baseUrl":"https://www.youtube.com/api/timedtext?lang=en&v=' . $videoId . '"}]}';
        $captionUrlWithFmt = 'https://www.youtube.com/api/timedtext?lang=en&v=' . $videoId . '&fmt=json3';
        $captionUrlRaw = 'https://www.youtube.com/api/timedtext?lang=en&v=' . $videoId;

        $urlFetcher = new MapUrlFetcher([
            $watchUrl => $watchHtml,
            $captionUrlWithFmt => null,
            $captionUrlRaw => null,
        ]);
        $fetcher = new YouTubeTranscriptFetcher($urlFetcher);

        $this->expectException(YouTubeCaptionContentFetchException::class);
        $this->expectExceptionMessage('Failed to fetch caption content');
        $fetcher->fetchAsVtt($videoId);
    }

    /**
     * extractCaptionTrackUrl second regex (unescaped baseUrl form) and caption URL without ? uses ?fmt=json3.
     */
    public function test_fetchAsVtt_unescaped_baseUrl_and_caption_url_without_query(): void
    {
        $videoId = 'noQuery';
        $watchUrl = 'https://www.youtube.com/watch?v=' . $videoId;
        $watchHtml = '{"other":1,"baseUrl" : "https://www.youtube.com/api/timedtext?lang=fr&v=' . $videoId . '"}';
        $captionUrlWithFmt = 'https://www.youtube.com/api/timedtext?lang=fr&v=' . $videoId . '&fmt=json3';
        $json3 = '{"events":[{"tStartMs":0,"dDurationMs":1000,"segs":[{"utf8":"Bonjour"}]}]}';

        $urlFetcher = new MapUrlFetcher([
            $watchUrl => $watchHtml,
            $captionUrlWithFmt => $json3,
        ]);
        $fetcher = new YouTubeTranscriptFetcher($urlFetcher);

        [$vtt, $language] = $fetcher->fetchAsVtt($videoId);
        $this->assertStringContainsString('Bonjour', $vtt);
        $this->assertSame('fr', $language);
    }

    /**
     * Caption URL without ? in baseUrl: fetcher appends ?fmt=json3.
     */
    public function test_fetchAsVtt_caption_baseUrl_without_query_string(): void
    {
        $videoId = 'rawPath';
        $watchUrl = 'https://www.youtube.com/watch?v=' . $videoId;
        $watchHtml = '{"captionTracks":[{"baseUrl":"https://www.youtube.com/api/timedtext"}]}';
        $captionUrlWithFmt = 'https://www.youtube.com/api/timedtext?fmt=json3';
        $json3 = '{"events":[{"tStartMs":0,"dDurationMs":500,"segs":[{"utf8":"Hi"}]}]}';

        $urlFetcher = new MapUrlFetcher([
            $watchUrl => $watchHtml,
            $captionUrlWithFmt => $json3,
        ]);
        $fetcher = new YouTubeTranscriptFetcher($urlFetcher);

        [$vtt, $language] = $fetcher->fetchAsVtt($videoId);
        $this->assertStringContainsString('Hi', $vtt);
        $this->assertNull($language);
    }

    /**
     * Plain text caption content is wrapped as single cue.
     */
    public function test_fetchAsVtt_plain_text_caption_wrapped_as_single_cue(): void
    {
        $videoId = 'plainTxt';
        $watchUrl = 'https://www.youtube.com/watch?v=' . $videoId;
        $watchHtml = '{"captionTracks":[{"baseUrl":"https://www.youtube.com/api/timedtext?lang=en&v=' . $videoId . '"}]}';
        $captionUrlWithFmt = 'https://www.youtube.com/api/timedtext?lang=en&v=' . $videoId . '&fmt=json3';
        $plainText = 'Just a line of text';

        $urlFetcher = new MapUrlFetcher([
            $watchUrl => $watchHtml,
            $captionUrlWithFmt => $plainText,
        ]);
        $fetcher = new YouTubeTranscriptFetcher($urlFetcher);

        [$vtt, $language] = $fetcher->fetchAsVtt($videoId);
        $this->assertStringStartsWith('WEBVTT', $vtt);
        $this->assertStringContainsString('00:00:00.000 --> 00:99:99.999', $vtt);
        $this->assertStringContainsString('Just a line of text', $vtt);
    }

    /**
     * Json3 with no events returns (No captions).
     */
    public function test_fetchAsVtt_json3_no_events_returns_no_captions(): void
    {
        $videoId = 'noEvents';
        $watchUrl = 'https://www.youtube.com/watch?v=' . $videoId;
        $watchHtml = '{"captionTracks":[{"baseUrl":"https://www.youtube.com/api/timedtext?lang=en&v=' . $videoId . '"}]}';
        $captionUrlWithFmt = 'https://www.youtube.com/api/timedtext?lang=en&v=' . $videoId . '&fmt=json3';
        $json3 = '{}';

        $urlFetcher = new MapUrlFetcher([
            $watchUrl => $watchHtml,
            $captionUrlWithFmt => $json3,
        ]);
        $fetcher = new YouTubeTranscriptFetcher($urlFetcher);

        [$vtt, $language] = $fetcher->fetchAsVtt($videoId);
        $this->assertStringContainsString('(No captions)', $vtt);
    }

    /**
     * Json3 with events that are skipped (missing fields / empty text) produces VTT with no cues.
     */
    public function test_fetchAsVtt_json3_all_events_skipped_produces_no_cues(): void
    {
        $videoId = 'skipAll';
        $watchUrl = 'https://www.youtube.com/watch?v=' . $videoId;
        $watchHtml = '{"captionTracks":[{"baseUrl":"https://www.youtube.com/api/timedtext?lang=en&v=' . $videoId . '"}]}';
        $captionUrlWithFmt = 'https://www.youtube.com/api/timedtext?lang=en&v=' . $videoId . '&fmt=json3';
        $json3 = '{"events":[{"tStartMs":0},{"segs":[{"utf8":"no tStartMs"}]},{"tStartMs":100,"dDurationMs":100,"segs":[]}]}';

        $urlFetcher = new MapUrlFetcher([
            $watchUrl => $watchHtml,
            $captionUrlWithFmt => $json3,
        ]);
        $fetcher = new YouTubeTranscriptFetcher($urlFetcher);

        [$vtt, $language] = $fetcher->fetchAsVtt($videoId);
        $this->assertStringStartsWith('WEBVTT', $vtt);
        $this->assertStringNotContainsString('00:00:00.000 --> 00:00:01.', $vtt);
    }

    /**
     * Srv1 invalid XML (content starts with <transcript so srv1 path is used) returns (Invalid caption format).
     */
    public function test_fetchAsVtt_srv1_invalid_xml_returns_invalid_format(): void
    {
        $videoId = 'badXml';
        $watchUrl = 'https://www.youtube.com/watch?v=' . $videoId;
        $watchHtml = '{"captionTracks":[{"baseUrl":"https://www.youtube.com/api/timedtext?lang=en&v=' . $videoId . '"}]}';
        $captionUrlWithFmt = 'https://www.youtube.com/api/timedtext?lang=en&v=' . $videoId . '&fmt=json3';
        $captionUrlRaw = 'https://www.youtube.com/api/timedtext?lang=en&v=' . $videoId;
        $invalidXml = '<transcript><unclosed>';

        $urlFetcher = new MapUrlFetcher([
            $watchUrl => $watchHtml,
            $captionUrlWithFmt => null,
            $captionUrlRaw => $invalidXml,
        ]);
        $fetcher = new YouTubeTranscriptFetcher($urlFetcher);

        [$vtt, $language] = $fetcher->fetchAsVtt($videoId);
        $this->assertStringContainsString('(Invalid caption format)', $vtt);
    }

    /**
     * Srv1 XML with only empty text elements produces VTT with no cues.
     */
    public function test_fetchAsVtt_srv1_empty_text_elements_produces_no_cues(): void
    {
        $videoId = 'emptySrv1';
        $watchUrl = 'https://www.youtube.com/watch?v=' . $videoId;
        $watchHtml = '{"captionTracks":[{"baseUrl":"https://www.youtube.com/api/timedtext?lang=en&v=' . $videoId . '"}]}';
        $captionUrlWithFmt = 'https://www.youtube.com/api/timedtext?lang=en&v=' . $videoId . '&fmt=json3';
        $captionUrlRaw = 'https://www.youtube.com/api/timedtext?lang=en&v=' . $videoId;
        $xml = '<?xml version="1.0"?><transcript><text start="0" dur="1"></text><text start="1" dur="1">   </text></transcript>';

        $urlFetcher = new MapUrlFetcher([
            $watchUrl => $watchHtml,
            $captionUrlWithFmt => null,
            $captionUrlRaw => $xml,
        ]);
        $fetcher = new YouTubeTranscriptFetcher($urlFetcher);

        [$vtt, $language] = $fetcher->fetchAsVtt($videoId);
        $this->assertStringStartsWith('WEBVTT', $vtt);
        $this->assertStringNotContainsString('00:00:00.000 --> 00:00:01.', $vtt);
    }
}

/**
 * UrlFetcher that returns fixed content per URL; throws UrlNotOkException for missing or null URLs.
 * Used to drive YouTubeTranscriptFetcher without network.
 */
final class MapUrlFetcher implements UrlFetcher
{
    /** @var array<string, string|null> */
    private array $urlToContent;

    /** @param array<string, string|null> $urlToContent */
    public function __construct(array $urlToContent)
    {
        $this->urlToContent = $urlToContent;
    }

    public function getUrl(string $uri): string
    {
        if (!array_key_exists($uri, $this->urlToContent)) {
            throw new UrlNotOkException('Not found: ' . $uri, 404);
        }
        $content = $this->urlToContent[$uri];
        if ($content === null) {
            throw new UrlNotOkException('Not found: ' . $uri, 404);
        }
        return $content;
    }
}
