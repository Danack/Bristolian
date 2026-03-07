<?php

declare(strict_types=1);

namespace Bristolian\Service\YouTube;

use Bristolian\Exception\YouTube\YouTubeCaptionContentFetchException;
use Bristolian\Exception\YouTube\YouTubeNoCaptionTracksException;
use Bristolian\Exception\YouTube\YouTubeWatchPageFetchException;
use UrlFetcher\UrlFetcher;
use UrlFetcher\UrlNotOkException;

/**
 * Fetches transcript/captions from YouTube using the unofficial timedtext/caption
 * data exposed on the watch page. No API key or OAuth required.
 */
class YouTubeTranscriptFetcher
{
    private const WATCH_URL_TEMPLATE = 'https://www.youtube.com/watch?v=%s';

    public function __construct(
        private UrlFetcher $urlFetcher
    ) {
    }

    /**
     * Fetch transcript for a YouTube video and return VTT content.
     *
     * @return array{0: string, 1: string|null} [vtt_content, language] or throws
     * @throws YouTubeNoCaptionTracksException When video has no caption tracks
     * @throws YouTubeWatchPageFetchException When watch page fetch fails
     * @throws YouTubeCaptionContentFetchException When caption content fetch fails
     */
    public function fetchAsVtt(string $youtubeVideoId): array
    {
        $watchHtml = $this->fetchWatchPage($youtubeVideoId);
        $captionTrackUrl = $this->extractCaptionTrackUrl($watchHtml);
        if ($captionTrackUrl === null) {
            throw YouTubeNoCaptionTracksException::forVideo($youtubeVideoId);
        }
        $language = $this->extractCaptionLanguageFromUrl($captionTrackUrl);
        $timedtextContent = $this->fetchCaptionContent($captionTrackUrl);
        $vtt = $this->convertTimedTextToVtt($timedtextContent);
        return [$vtt, $language];
    }

    private function fetchWatchPage(string $youtubeVideoId): string
    {
        $url = sprintf(self::WATCH_URL_TEMPLATE, $youtubeVideoId);
        try {
            return $this->urlFetcher->getUrl($url);
        } catch (UrlNotOkException $e) {
            throw YouTubeWatchPageFetchException::fromUrlFailure($e->getMessage(), $e);
        }
    }

    private function extractCaptionTrackUrl(string $html): ?string
    {
        // YouTube embeds captionTracks in the page; baseUrl can be "https://..." or \"https:\\/\\/...\"
        if (preg_match('/"captionTracks"\s*:\s*\[\s*\{[^}]*"baseUrl"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"/', $html, $matches)) {
            $url = $matches[1];
            $url = str_replace(['\\/', '\\"'], ['/', '"'], $url);
            if (str_starts_with($url, 'http')) {
                return $url;
            }
        }
        // Unescaped form
        if (preg_match('/"baseUrl"\s*:\s*"(https:[^"]+timedtext[^"]*)"/', $html, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function extractCaptionLanguageFromUrl(string $url): ?string
    {
        if (preg_match('/[?&]lang=([a-zA-Z_-]+)/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function fetchCaptionContent(string $captionTrackUrl): string
    {
        // Request JSON3 format for structured events
        $url = $captionTrackUrl;
        if (strpos($url, '?') !== false) {
            $url .= '&fmt=json3';
        }
        else {
            $url .= '?fmt=json3';
        }
        try {
            return $this->urlFetcher->getUrl($url);
        }
        catch (UrlNotOkException $e) {
            // Fallback: try without fmt=json3 (some return srv1 XML)
            try {
                return $this->urlFetcher->getUrl($captionTrackUrl);
            } catch (UrlNotOkException $e2) {
                throw YouTubeCaptionContentFetchException::fromUrlFailure($e2->getMessage(), $e2);
            }
        }
    }

    /**
     * Convert YouTube timedtext content (json3 or srv1 XML) to WebVTT.
     */
    private function convertTimedTextToVtt(string $content): string
    {
        $trimmed = trim($content);
        if (str_starts_with($trimmed, '{')) {
            return $this->convertJson3ToVtt($trimmed);
        }
        if (str_starts_with($trimmed, '<?xml') || str_starts_with($trimmed, '<transcript')) {
            return $this->convertSrv1XmlToVtt($trimmed);
        }
        // Plain text or unknown: wrap as single cue
        return "WEBVTT\n\n00:00:00.000 --> 00:99:99.999\n" . $content . "\n";
    }

    private function convertJson3ToVtt(string $json): string
    {
        $data = json_decode($json, true);
        if (!is_array($data) || !isset($data['events'])) {
            return "WEBVTT\n\n00:00:00.000 --> 00:00:01.000\n(No captions)\n";
        }
        $out = "WEBVTT\n\n";
        foreach ($data['events'] as $event) {
            if (!isset($event['tStartMs'], $event['dDurationMs']) || !isset($event['segs'])) {
                continue;
            }
            $startMs = (int) $event['tStartMs'];
            $durationMs = (int) $event['dDurationMs'];
            $endMs = $startMs + $durationMs;
            $text = '';
            foreach ($event['segs'] as $segment) {
                if (isset($segment['utf8']) && is_string($segment['utf8'])) {
                    $text .= $segment['utf8'];
                }
            }
            $text = trim(str_replace("\n", ' ', $text));
            if ($text === '') {
                continue;
            }
            $out .= self::msToVttTimestamp($startMs) . ' --> ' . self::msToVttTimestamp($endMs) . "\n";
            $out .= $text . "\n\n";
        }
        return rtrim($out) ?: "WEBVTT\n\n00:00:00.000 --> 00:00:01.000\n(No captions)\n";
    }

    private function convertSrv1XmlToVtt(string $xml): string
    {
        $dom = @new \DOMDocument();
        if (@$dom->loadXML($xml) === false) {
            return "WEBVTT\n\n00:00:00.000 --> 00:00:01.000\n(Invalid caption format)\n";
        }
        $texts = $dom->getElementsByTagName('text');
        $out = "WEBVTT\n\n";
        foreach ($texts as $element) {
            $start = (float) $element->getAttribute('start');
            $duration = (float) $element->getAttribute('dur');
            $end = $start + $duration;
            $text = html_entity_decode($element->textContent, ENT_HTML5 | ENT_QUOTES, 'UTF-8');
            $text = trim(str_replace("\n", ' ', $text));
            if ($text === '') {
                continue;
            }
            $out .= self::msToVttTimestamp((int) round($start * 1000)) . ' --> ' . self::msToVttTimestamp((int) round($end * 1000)) . "\n";
            $out .= $text . "\n\n";
        }
        return rtrim($out) ?: "WEBVTT\n\n00:00:00.000 --> 00:00:01.000\n(No captions)\n";
    }

    private static function msToVttTimestamp(int $milliseconds): string
    {
        $total_seconds = (int) floor($milliseconds / 1000);
        $millis = $milliseconds % 1000;
        $minutes_full = (int) floor($total_seconds / 60);
        $seconds = $total_seconds % 60;
        $hours = (int) floor($minutes_full / 60);
        $minutes = $minutes_full % 60;
        return sprintf('%02d:%02d:%02d.%03d', $hours, $minutes, $seconds, $millis);
    }
}
